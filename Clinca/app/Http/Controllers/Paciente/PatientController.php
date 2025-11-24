<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Document;
use App\Models\Treatment;
use App\Models\MedicalHistory;
use App\Models\VitalSign;
use App\Models\Reminder;
use App\Models\Allergy;

class PatientController extends Controller
{
    // Return unified history events for a patient with detailed medical information
    public function history(Request $request)
    {
        $patient = $this->resolvePatient($request);
        if (!$patient) return response()->json([], 200);

        $record = $patient->record;
        $items = [];

        if ($record) {
            $rid = $record->id;

            // Get medical histories (these contain the actual diagnosis records)
            $medHistories = MedicalHistory::where('record_id', $rid)
                ->orderBy('recorded_at', 'desc')
                ->get();
            
            foreach ($medHistories as $medHistory) {
                $dt = $medHistory->recorded_at ? substr($medHistory->recorded_at, 0, 10) : 
                      ($medHistory->created_at ? substr($medHistory->created_at, 0, 10) : null);
                
                // Format date for display (DD/MM/YYYY)
                $fechaDisplay = $dt ? date('d/m/Y', strtotime($dt)) : '—';
                
                // Get clinician name with Dr. prefix and last name only
                $clinician = \App\Models\User::find($medHistory->recorded_by);
                $doctorName = 'Doctor no asignado';
                if ($clinician) {
                    // Extract last name from full name (assuming format: "FirstName LastName")
                    $nameParts = explode(' ', trim($clinician->name));
                    $lastName = count($nameParts) > 1 ? end($nameParts) : $clinician->name;
                    $doctorName = 'Dr. ' . $lastName;
                }
                
                // Find encounter on same date to get vital signs
                $encounter = Encounter::where('record_id', $rid)
                    ->whereDate('encounter_dt', $dt)
                    ->first();
                
                $vitals = null;
                if ($encounter) {
                    $vitals = VitalSign::where('encounter_id', $encounter->id)->first();
                }
                
                // Get all allergies for this patient
                $allergies = Allergy::where('record_id', $rid)
                    ->pluck('allergen')
                    ->toArray();
                $allergiesStr = !empty($allergies) ? implode(', ', $allergies) : 'Ninguna registrada';
                
                // Get antecedentes from medical_background field
                $antecedentes = $medHistory->medical_background ?? 'Sin antecedentes registrados';
                
                // Get treatments active around this date
                $treatments = Treatment::where('record_id', $rid)
                    ->where(function($q) use ($dt) {
                        $q->whereDate('start_dt', '<=', $dt)
                          ->where(function($q2) use ($dt) {
                              $q2->whereNull('end_dt')
                                 ->orWhereDate('end_dt', '>=', $dt);
                          });
                    })
                    ->get();
                
                $treatmentStr = $treatments->map(function($t) {
                    return ($t->name ?? 'Tratamiento') . ' ' . ($t->dose ?? '') . 
                           ($t->instructions ? ' - ' . $t->instructions : '');
                })->implode('; ');
                
                if (empty($treatmentStr)) {
                    $treatmentStr = 'Sin tratamiento registrado';
                }
                
                // Get documents around this date (within 7 days)
                $dateStart = date('Y-m-d', strtotime($dt . ' -7 days'));
                $dateEnd = date('Y-m-d', strtotime($dt . ' +7 days'));
                
                $documents = Document::where('record_id', $rid)
                    ->whereBetween('created_at', [$dateStart, $dateEnd])
                    ->get();
                
                $docsArray = $documents->map(function($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->title ?? $d->doc_type ?? 'Documento',
                        'uri' => $d->storage_uri ?? ''
                    ];
                })->toArray();
                
                $docsStr = !empty($docsArray) ? json_encode($docsArray) : '';
                
                // Build comprehensive history item
                $items[] = [
                    'fecha' => $fechaDisplay,
                    'motivo' => $medHistory->details ?? 'Consulta general',
                    'diagnostico' => $medHistory->condition ?? 'Sin diagnóstico registrado',
                    'doctor' => $doctorName,
                    'doctor_id' => $medHistory->recorded_by,
                    'alergias' => $allergiesStr,
                    'antecedentes' => $antecedentes,
                    'temperatura' => $vitals && $vitals->temp_c ? $vitals->temp_c . ' °C' : '—',
                    'presion' => $vitals && $vitals->sbp && $vitals->dbp ? 
                                 $vitals->sbp . '/' . $vitals->dbp . ' mmHg' : '—',
                    'pulso' => $vitals && $vitals->hr ? $vitals->hr . ' lpm' : '—',
                    'frecuencia_resp' => $vitals && $vitals->rr ? $vitals->rr . ' rpm' : '—',
                    'saturacion_ox' => $vitals && $vitals->spo2 ? $vitals->spo2 . ' %' : '—',
                    'tratamiento' => $treatmentStr,
                    'documentos' => $docsStr,
                    'history_id' => $medHistory->id,
                    'fecha_raw' => $dt // for sorting
                ];
            }
        }

        // Sort by fecha desc
        usort($items, function($a, $b) { 
            return strcmp($b['fecha_raw'] ?? '', $a['fecha_raw'] ?? ''); 
        });

        // Remove fecha_raw before returning
        foreach ($items as &$item) {
            unset($item['fecha_raw']);
        }

        return response()->json(array_values($items));
    }

    // Return reminders/citas for a patient
    public function reminders(Request $request)
    {
        $patient = $this->resolvePatient($request);
        if (!$patient) return response()->json([], 200);

        $appts = Appointment::where('patient_id', $patient->id)
            ->orderBy('scheduled_at', 'asc')
            ->get();
        
        $rows = [];
        $today = now();
        
        foreach ($appts as $a) {
            $fecha = $a->scheduled_at ? substr($a->scheduled_at, 0, 10) : null;
            $hora  = $a->scheduled_at ? substr($a->scheduled_at, 11, 5) : null;
            $fechaDisplay = $fecha ? date('d/m/Y', strtotime($fecha)) : '—';
            
            $estado = $a->status ?? 'pending';
            
            // Get clinician name with Dr. prefix and last name only
            $clinician = \App\Models\User::find($a->clinician_id);
            $doctorName = 'Doctor';
            if ($clinician) {
                // Extract last name from full name (assuming format: "FirstName LastName")
                $nameParts = explode(' ', trim($clinician->name));
                $lastName = count($nameParts) > 1 ? end($nameParts) : $clinician->name;
                $doctorName = 'Dr. ' . $lastName;
            }
            
            $appointmentDate = $fecha ? \Carbon\Carbon::parse($fecha . ' ' . $hora) : null;
            
            // Determine status for display
            $estadoDisplay = 'Próxima';
            $detalle = '';
            
            if ($estado === 'cancelled') {
                $estadoDisplay = 'Cancelada';
                $detalle = "Tu cita con {$doctorName} fue cancelada";
            } elseif ($estado === 'no_show') {
                $estadoDisplay = 'No asistió';
                $detalle = "Faltaste a tu cita con {$doctorName} el {$fechaDisplay}";
            } elseif ($estado === 'completed') {
                $estadoDisplay = 'Completada';
                $detalle = "Cita completada con {$doctorName}";
            } elseif ($appointmentDate) {
                if ($appointmentDate->isToday()) {
                    $estadoDisplay = 'Hoy';
                    $detalle = "¡Tienes una cita HOY con {$doctorName} a las {$hora}!";
                } elseif ($appointmentDate->isTomorrow()) {
                    $estadoDisplay = 'Mañana';
                    $detalle = "Tienes una cita mañana con {$doctorName} a las {$hora}";
                } elseif ($appointmentDate->isFuture()) {
                    $estadoDisplay = 'Próxima';
                    $detalle = "Cita con {$doctorName} el {$fechaDisplay} a las {$hora}";
                } else {
                    // Past appointment
                    $estadoDisplay = 'Pasada';
                    $detalle = "Cita pasada con {$doctorName}";
                }
            } else {
                $detalle = "Cita con {$doctorName}";
            }
            
            $rows[] = [
                'fecha' => $fechaDisplay,
                'hora' => $hora,
                'tipo' => 'Cita',
                'detalle' => $detalle,
                'estado' => $estadoDisplay,
                'motivo' => $a->reason ?? 'Consulta general',
                'doctor' => $doctorName,
                'appointment_id' => $a->id,
                'status_code' => $estado
            ];
        }

        // Sort: upcoming first, then past
        usort($rows, function($a, $b) {
            $aFuture = in_array($a['estado'], ['Hoy', 'Mañana', 'Próxima']);
            $bFuture = in_array($b['estado'], ['Hoy', 'Mañana', 'Próxima']);
            
            if ($aFuture && !$bFuture) return -1;
            if (!$aFuture && $bFuture) return 1;
            
            return strcmp($a['fecha'] . $a['hora'], $b['fecha'] . $b['hora']);
        });
        
        return response()->json(array_values($rows));
    }

    // Persist notification preferences (best-effort) for the authenticated patient
    public function notifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error'=>'Unauthenticated'], 403);

        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) return response()->json(['error'=>'Patient not found'], 404);

        $data = $request->validate([
            'enabled' => 'required|boolean',
            'email' => 'required|boolean',
            'phone' => 'required|boolean',
        ]);

        $key = 'patient_notifications_' . $patient->id;
        Cache::put($key, $data, 60*24*365); // 1 year

        return response()->json(['saved'=>true]);
    }

    // Get notification preferences for the authenticated patient
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error'=>'Unauthenticated'], 403);

        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) return response()->json(['error'=>'Patient not found'], 404);

        $key = 'patient_notifications_' . $patient->id;
        $prefs = Cache::get($key, [
            'enabled' => true,
            'email' => true,
            'phone' => false
        ]);

        return response()->json($prefs);
    }

    // Download a document for the authenticated patient
    public function downloadDocument(Request $request, $id)
    {
        $patient = $this->resolvePatient($request);
        if (!$patient) {
            return response()->json(['error' => 'Unauthenticated'], 403);
        }

        // Find the document
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Verify the document belongs to this patient's medical record
        $record = $patient->record;
        if (!$record || $document->record_id !== $record->id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Get the file path from storage_uri - try multiple possible locations
        $storageUri = $document->storage_uri;
        
        // Try different path combinations
        $possiblePaths = [
            storage_path('app/' . $storageUri),
            storage_path('app/public/' . $storageUri),
            public_path('storage/' . $storageUri),
            public_path($storageUri),
            base_path($storageUri)
        ];
        
        $filePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }
        
        if (!$filePath) {
            Log::error('Document file not found', [
                'document_id' => $id,
                'storage_uri' => $storageUri,
                'tried_paths' => $possiblePaths
            ]);
            return response()->json([
                'error' => 'File not found on server',
                'storage_uri' => $storageUri,
                'tried_paths' => $possiblePaths
            ], 404);
        }

        // Get the original filename or create one
        $filename = $document->title ?? basename($filePath);
        
        // Add file extension if missing
        if (!pathinfo($filename, PATHINFO_EXTENSION)) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if ($ext) {
                $filename .= '.' . $ext;
            }
        }
        
        // Return the file as a download
        return response()->download($filePath, $filename);
    }

    protected function resolvePatient(Request $request)
    {
        $pid = $request->query('patient_id') ?? $request->input('patient_id') ?? null;
        if ($pid) {
            return Patient::find($pid);
        }
        // try authenticated user -> patient
        if (Auth::check()){
            return Patient::where('user_id', Auth::id())->first();
        }
        return null;
    }
}
