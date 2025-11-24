<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Document;
use App\Models\Treatment;
use App\Models\MedicalHistory;
use App\Models\VitalSign;
use App\Models\Allergy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
 

class MedicoController extends Controller
{
    protected function ensureMedicoOrAdmin()
    {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);

        if (!in_array($role, ['medico', 'administrador'])) {
            abort(403, 'Acceso no autorizado');
        }
    }

    // Dashboard statistics
    public function dashboardStats(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        
        $today = now()->toDateString();
        $medicoId = Auth::id();
        
        // Today's appointments for this doctor
        $citasHoy = Appointment::where('clinician_id', $medicoId)
            ->whereDate('scheduled_at', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        
        // Appointments per day this month for chart
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        
        $appointmentsByDay = Appointment::where('clinician_id', $medicoId)
            ->whereBetween('scheduled_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
            ->selectRaw('DATE(scheduled_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->date => $item->count];
            });
        
        // Generate array for last 7 days
        $chartData = [];
        $dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $dayOfWeek = $date->dayOfWeek;
            
            $chartData[] = [
                'label' => $dayNames[$dayOfWeek],
                'date' => $dateStr,
                'value' => $appointmentsByDay[$dateStr] ?? 0
            ];
        }
        
        return response()->json([
            'citas_hoy' => $citasHoy,
            'chart_data' => $chartData
        ]);
    }

    // Search patients by name or id (used by medico panel)
    public function searchPatients(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $q = $request->query('query');
        if (!$q) return response()->json([], 200);

        $medicoId = Auth::id();
        
        // Get patient IDs that have appointments with this doctor
        $patientIdsWithAppointments = Appointment::where('clinician_id', $medicoId)
            ->distinct()
            ->pluck('patient_id')
            ->toArray();
        
        if (empty($patientIdsWithAppointments)) {
            return response()->json([], 200);
        }

        $query = Patient::whereIn('id', $patientIdsWithAppointments);
        
        // Accept numeric ids or UUIDs in the query
        $isUuid = preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $q);
        if (is_numeric($q) || $isUuid) {
            $query->where('id', $q);
        } else {
            $qClean = trim($q);
            $terms = preg_split('/\s+/', $qClean);

            $query->where(function($qq) use ($qClean, $terms) {
                // match simple substrings on first_name/last_name/email/curp
                $qq->where('first_name', 'like', "%{$qClean}%")
                   ->orWhere('last_name', 'like', "%{$qClean}%")
                   ->orWhere('email', 'like', "%{$qClean}%")
                   ->orWhere('curp', 'like', "%{$qClean}%")
                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$qClean}%"])
                   ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$qClean}%"]);

                // If query has at least two parts, try first/last match
                if (count($terms) >= 2) {
                    $first = $terms[0];
                    $last = $terms[count($terms)-1];
                    $qq->orWhere(function($q2) use ($first, $last){
                        $q2->where('first_name', 'like', "%{$first}%")->where('last_name', 'like', "%{$last}%");
                    });
                    // also try reversed order
                    $qq->orWhere(function($q3) use ($first, $last){
                        $q3->where('first_name', 'like', "%{$last}%")->where('last_name', 'like', "%{$first}%");
                    });
                }
            });
        }

        $list = $query->limit(10)->get()->map(function($p){
            // compute age if possible (model uses dob)
            $age = null;
            if (!empty($p->dob)) {
                try { $age = \Carbon\Carbon::parse($p->dob)->age . ' años'; } catch(\Throwable $e) { $age = null; }
            }
            // last consult from appointments or encounters
            $lastAppt = Appointment::where('patient_id', $p->id)->orderByDesc('scheduled_at')->value('scheduled_at');
            $lastEnc = null;
            if ($p->record && $p->record->id) {
                $lastEnc = Encounter::where('record_id', $p->record->id)->orderByDesc('encounter_dt')->value('encounter_dt');
            }
            $last = $lastAppt ?: $lastEnc ?: null;
            
            // Get most recent diagnosis from medical_histories
            $diagnosis = null;
            if ($p->record && $p->record->id) {
                $recentHistory = \App\Models\MedicalHistory::where('record_id', $p->record->id)
                    ->orderByDesc('recorded_at')
                    ->first();
                $diagnosis = $recentHistory ? $recentHistory->condition : null;
            }
            
            $fullName = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
            return [
                'id' => $p->id,
                'name' => $fullName ?: null,
                'gender' => $p->sex ?? null,
                'age' => $age,
                'diagnosis' => $diagnosis,
                'last_consult' => $last ? substr($last,0,10) : null,
            ];
        });

        return response()->json($list);
    }

    // GET medico/api/history?patient_id=XX or ?diagnosis=XX
    public function historial(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        $diagnosis = $request->query('diagnosis') ?? $request->input('diagnosis');
        
        // If searching by diagnosis, get all patients with that diagnosis
        if ($diagnosis && !$pid) {
            $medicoId = Auth::id();
            
            // Get patient IDs that have appointments with this doctor
            $patientIdsWithAppointments = Appointment::where('clinician_id', $medicoId)
                ->distinct()
                ->pluck('patient_id')
                ->toArray();
            
            if (empty($patientIdsWithAppointments)) {
                return response()->json([]);
            }
            
            // Get medical records for these patients
            $recordIds = MedicalRecord::whereIn('patient_id', $patientIdsWithAppointments)
                ->pluck('id')
                ->toArray();
            
            if (empty($recordIds)) {
                return response()->json([]);
            }
            
            // Find medical histories with this diagnosis
            $medicalHistories = MedicalHistory::whereIn('record_id', $recordIds)
                ->where('condition', 'LIKE', '%' . $diagnosis . '%')
                ->get();
            
            $items = [];
            foreach ($medicalHistories as $mh) {
                $record = MedicalRecord::find($mh->record_id);
                if (!$record) continue;
                
                $patient = Patient::find($record->patient_id);
                if (!$patient) continue;
                
                $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                $dt = $mh->recorded_at ? substr($mh->recorded_at, 0, 10) : ($mh->created_at ? substr($mh->created_at, 0, 10) : null);
                
                $autor = null;
                if ($mh->recorded_by) {
                    try { $autor = DB::table('users')->where('id', $mh->recorded_by)->value('name'); } catch(\Throwable $e) { }
                }
                
                $items[] = [
                    'fecha' => $dt,
                    'paciente' => $patientName,
                    'tipo' => 'Historial',
                    'detalle' => $mh->details ?? '',
                    'diagnostico' => $mh->condition ?? null,
                    'autor' => $autor
                ];
            }
            
            usort($items, function($a,$b){ return strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''); });
            return response()->json(array_values($items));
        }

        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient) return response()->json([], 200);

        $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));

        $record = $patient->record;
        $items = [];

        // Appointments
        $appts = Appointment::where('patient_id', $patient->id)->get();
        foreach ($appts as $a) {
            $dt = $a->scheduled_at ? substr($a->scheduled_at,0,10) : ($a->created_at?substr($a->created_at,0,10):null);
            $autor = null;
            if ($a->created_by) {
                try { $autor = DB::table('users')->where('id', $a->created_by)->value('name'); } catch(\Throwable $e) { }
            }
            $items[] = [
                'id' => $a->id,
                'fecha'=>$dt,
                'paciente'=>$patientName,
                'tipo'=>'Cita',
                'detalle'=>trim(($a->reason ?? '') . ' (' . ($a->status ?? '') . ')'),
                'diagnostico'=>null,
                'autor'=>$autor,
                'motivo' => $a->reason ?? ''
            ];
        }

        if ($record) {
            $rid = $record->id;

            $enc = Encounter::where('record_id', $rid)->get();
            foreach ($enc as $e){
                $dt = $e->encounter_dt ? substr($e->encounter_dt,0,10) : ($e->created_at?substr($e->created_at,0,10):null);
                $autor = null;
                if ($e->clinician_id) {
                    try { $autor = DB::table('users')->where('id', $e->clinician_id)->value('name'); } catch(\Throwable $ex) { }
                }
                $items[] = [
                    'id' => $e->id,
                    'fecha'=>$dt,
                    'paciente'=>$patientName,
                    'tipo'=>'Encuentro',
                    'detalle'=>($e->reason ?? '') . ' ' . ($e->notes ?? ''),
                    'diagnostico'=>null,
                    'autor'=>$autor,
                    'motivo' => $e->reason ?? '',
                    'notas' => $e->notes ?? ''
                ];
            }

            $mh = MedicalHistory::where('record_id', $rid)->get();
            foreach ($mh as $m){
                $dt = $m->recorded_at ? substr($m->recorded_at,0,10) : ($m->created_at?substr($m->created_at,0,10):null);
                $autor = null;
                if ($m->recorded_by) {
                    try { $autor = DB::table('users')->where('id', $m->recorded_by)->value('name'); } catch(\Throwable $e) { }
                }
                
                // Get allergies for this patient
                $allergies = Allergy::where('record_id', $rid)->pluck('allergen')->toArray();
                $allergiesStr = !empty($allergies) ? implode(', ', $allergies) : null;
                
                $items[] = [
                    'id' => $m->id,
                    'fecha'=>$dt,
                    'paciente'=>$patientName,
                    'tipo'=>'Historial',
                    'detalle'=>$m->details ?? '',
                    'diagnostico'=>$m->condition ?? null,
                    'autor'=>$autor,
                    'motivo' => $m->details ?? '',
                    'antecedentes' => $m->medical_background ?? null,
                    'alergias' => $allergiesStr
                ];
            }

            $docs = Document::where('record_id', $rid)->get();
            foreach ($docs as $d){
                $dt = $d->created_at ? substr($d->created_at,0,10) : null;
                $autor = null;
                if ($d->uploaded_by) {
                    try { $autor = DB::table('users')->where('id', $d->uploaded_by)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = [
                    'fecha'=>$dt,
                    'paciente'=>$patientName,
                    'tipo'=>'Documento',
                    'detalle'=>($d->title ?? $d->doc_type ?? ''),
                    'diagnostico'=>null,
                    'autor'=>$autor
                ];
            }

            $treats = Treatment::where('record_id', $rid)->get();
            foreach ($treats as $t){
                $dt = $t->start_dt ? substr($t->start_dt,0,10) : ($t->created_at?substr($t->created_at,0,10):null);
                $autor = null;
                if ($t->updated_by) {
                    try { $autor = DB::table('users')->where('id', $t->updated_by)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = [
                    'id' => $t->id,
                    'fecha'=>$dt,
                    'paciente'=>$patientName,
                    'tipo'=>'Tratamiento',
                    'detalle'=>($t->name ?? '') . ' ' . ($t->dose ?? ''),
                    'diagnostico'=>null,
                    'autor'=>$autor,
                    'tratamiento' => ($t->name ?? '') . ' - ' . ($t->dose ?? '') . ' ' . ($t->frequency ?? ''),
                    'notas' => $t->instructions ?? ''
                ];
            }

            $vitals = VitalSign::whereIn('encounter_id', $enc->pluck('id')->toArray())->get();
            foreach ($vitals as $v){
                $dt = $v->taken_at ? substr($v->taken_at,0,10) : ($v->created_at?substr($v->created_at,0,10):null);
                $autor = null;
                if ($v->nurse_id) {
                    try { $autor = DB::table('users')->where('id', $v->nurse_id)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = [
                    'id' => $v->id,
                    'fecha'=>$dt,
                    'paciente'=>$patientName,
                    'tipo'=>'Signos vitales',
                    'detalle'=>('TA ' . ($v->sbp ?? '') . '/' . ($v->dbp ?? '') . ' · Temp ' . ($v->temp_c ?? '')),
                    'diagnostico'=>null,
                    'autor'=>$autor,
                    'temperatura' => $v->temp_c ?? null,
                    'presion' => ($v->sbp && $v->dbp) ? $v->sbp . '/' . $v->dbp : null,
                    'pulso' => $v->hr ?? null,
                    'frecuencia_respiratoria' => $v->rr ?? null,
                    'spo2' => $v->spo2 ?? null,
                    'peso' => $v->weight_kg ?? null,
                    'altura' => $v->height_cm ?? null
                ];
            }
        }

        usort($items, function($a,$b){ return strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''); });
        return response()->json(array_values($items));
    }

    // GET medico/api/documentos?patient_id=XX
    public function documentos(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient || !$patient->record) return response()->json([], 200);

        $docs = Document::where('record_id', $patient->record->id)->orderByDesc('created_at')->get();
        $out = $docs->map(function($d){
            $uploader = null;
            try{ $uploader = DB::table('users')->where('id', $d->uploaded_by)->value('name'); } catch(\Throwable $e){ $uploader = null; }
            return [
                'id' => $d->id,
                'title' => $d->title,
                'doc_type' => $d->doc_type,
                'storage_uri' => $d->storage_uri,
                'uploaded_by' => $d->uploaded_by,
                'uploader_name' => $uploader,
                'created_at' => $d->created_at ? $d->created_at->toDateTimeString() : null,
            ];
        });
        return response()->json($out);
    }

    // GET medico/api/documentos/{id}/download
    public function downloadDocument($documentId)
    {
        $this->ensureMedicoOrAdmin();
        
        $document = Document::find($documentId);
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($document->storage_uri)) {
            return response()->json(['error' => 'File not found in storage'], 404);
        }
        
        $filePath = Storage::disk('public')->path($document->storage_uri);
        
        // Extract file extension from the stored file path
        $extension = pathinfo($document->storage_uri, PATHINFO_EXTENSION);
        $fileName = $document->title . ($extension ? '.' . $extension : '');
        
        return response()->download($filePath, $fileName);
    }

    // GET medico/api/tratamientos?patient_id=XX
    public function tratamientos(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient || !$patient->record) return response()->json([], 200);

        $t = Treatment::where('record_id', $patient->record->id)->get();
        return response()->json($t);
    }

    // POST medico/api/encounters  -> create encounter (alta historial)
    public function storeEncounter(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $data = $request->validate([
            'patient_id' => 'required', // patients use UUIDs
            'record_id'  => 'nullable',
            'encounter_dt' => 'nullable|date',
            'reason' => 'required|string',
            'notes'  => 'nullable|string',
        ]);

        $patient = Patient::find($data['patient_id']);
        if (!$patient) return response()->json(['error'=>'patient not found'], 404);

        $recordId = $data['record_id'] ?? ($patient->record ? $patient->record->id : null);
        if (!$recordId) return response()->json(['error'=>'medical record missing'], 400);

        $enc = Encounter::create([
            'record_id' => $recordId,
            'encounter_dt' => $data['encounter_dt'] ?? now(),
            'reason' => $data['reason'],
            'notes'  => $data['notes'] ?? null,
        ]);

        return response()->json(['created'=>true,'id'=>$enc->id], 201);
    }

    // POST /medico/api/documentos  -> upload one or more documents
    public function uploadDocuments(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $data = $request->validate([
            'patient_id' => 'required', // patients use UUIDs
            'title' => 'nullable|string|max:255',
            'doc_type' => 'nullable|string|max:100',
            'files.*' => 'required|file|max:15360' // 15MB per file
        ]);

        $patient = Patient::find($data['patient_id']);
        if (!$patient) return response()->json(['error'=>'patient not found'], 404);
        $recordId = $patient->record ? $patient->record->id : null;
        if (!$recordId) return response()->json(['error'=>'medical record missing'], 400);

        $saved = [];
        if ($request->hasFile('files')){
            foreach ($request->file('files') as $f){
                try{
                    $orig = $f->getClientOriginalName();
                    $name = Str::random(20) . '_' . preg_replace('/[^A-Za-z0-9_.-]/','_', $orig);
                    $path = $f->storeAs('documents', $name, 'public');

                    $doc = new Document();
                    $doc->record_id = $recordId;
                    $doc->title = $data['title'] ?? $orig;
                    $doc->doc_type = $data['doc_type'] ?? pathinfo($orig, PATHINFO_EXTENSION);
                    // store using model's expected fields: storage_uri and uploaded_by
                    $doc->storage_uri = $path;
                    $doc->uploaded_by = Auth::id();
                    $doc->save();

                    $saved[] = ['id'=>$doc->id,'title'=>$doc->title,'storage_uri'=>$doc->storage_uri];
                }catch(\Throwable $e){
                    Log::error('Upload document failed: '.$e->getMessage());
                }
            }
        }

        return response()->json(['saved'=>$saved]);
    }

    // POST /medico/api/tratamientos -> create a treatment for patient's record
    public function storeTreatment(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $data = $request->validate([
            'patient_id' => 'required', // patients use UUIDs, accept string or numeric ids
            'record_id' => 'nullable',
            'name' => 'required|string|max:255',
            'dose' => 'nullable|string|max:255',
            'start_dt' => 'nullable|date',
            'notes' => 'nullable|string' // will be stored in route field
        ]);

        $patient = Patient::find($data['patient_id']);
        if (!$patient) return response()->json(['error'=>'patient not found'], 404);

        $recordId = $data['record_id'] ?? ($patient->record ? $patient->record->id : null);
        if (!$recordId) return response()->json(['error'=>'medical record missing'], 400);

    $t = new Treatment();
        $t->record_id = $recordId;
        $t->name = $data['name'];
        $t->dose = $data['dose'] ?? null;
        $t->route = $data['notes'] ?? null; // store notes in route field as workaround
        $t->start_dt = $data['start_dt'] ?? now();
        $t->status = 'activo';
    // model uses 'updated_by' in schema; set that to current user
    $t->updated_by = Auth::id();
        $t->save();

        return response()->json(['created'=>true,'id'=>$t->id]);
    }

    public function vitals(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient || !$patient->record) return response()->json([], 200);

        // find encounters for record
        $recordId = $patient->record->id;
        $encIds = Encounter::where('record_id', $recordId)->pluck('id')->toArray();
        $v = VitalSign::whereIn('encounter_id', $encIds)->orderByDesc('taken_at')->limit(30)->get();

        // build a map of nurse_id => name for authors
        $nurseIds = $v->pluck('nurse_id')->filter()->unique()->toArray();
        $nurseMap = [];
        if (!empty($nurseIds)) {
            $names = DB::table('users')->whereIn('id', $nurseIds)->select('id','name')->get();
            foreach($names as $nn) $nurseMap[$nn->id] = $nn->name;
        }

        $out = $v->map(function($s) use ($nurseMap){
            return [
                'id'=>$s->id,
                'fecha'=> $s->taken_at ? substr($s->taken_at,0,10) : ($s->created_at?substr($s->created_at,0,10):null),
                'hora' => $s->taken_at ? substr($s->taken_at,11,5) : null,
                'temp' => $s->temp_c,
                'sbp'  => $s->sbp,
                'dbp'  => $s->dbp,
                'pulso'=> $s->hr,
                'fr'   => $s->rr,
                'spo2' => $s->spo2, // Use correct field name
                'peso' => $s->weight_kg,
                'talla'=> $s->height_cm,
                'author'=> $nurseMap[$s->nurse_id] ?? 'N/A'
            ];
        });

        return response()->json($out->toArray());
    }

    public function storeAltaHistorial(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        
        $data = $request->validate([
            'patient_id' => 'required|string',
            'encounter_dt' => 'required|date',
            'reason' => 'nullable|string', // motivo/observaciones
            'diagnosis' => 'required|string', // diagnóstico
            'allergies' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            'vitals' => 'nullable|array',
            'vitals.temp' => 'nullable|numeric',
            'vitals.sbp' => 'nullable|integer',
            'vitals.dbp' => 'nullable|integer',
            'vitals.hr' => 'nullable|integer',
            'vitals.rr' => 'nullable|integer',
            'vitals.spo2' => 'nullable|integer',
            'vitals.weight' => 'nullable|numeric',
            'vitals.height' => 'nullable|numeric'
        ]);

        // Find patient and ensure they have a medical record
        $patient = Patient::find($data['patient_id']);
        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        if (!$patient->record) {
            return response()->json(['error' => 'Patient has no medical record'], 404);
        }

        $recordId = $patient->record->id;
        $currentUserId = Auth::id();
        $encounterDate = $data['encounter_dt'];

        // Create or find encounter for this date
        $encounter = Encounter::where('record_id', $recordId)
            ->whereDate('encounter_dt', $encounterDate)
            ->first();

        if (!$encounter) {
            $encounter = new Encounter();
            $encounter->record_id = $recordId;
            $encounter->encounter_dt = $encounterDate;
            $encounter->reason = $data['reason'] ?? 'Consulta médica';
            $encounter->clinician_id = $currentUserId;
            $encounter->save();
        } else {
            // Update reason if provided
            if (!empty($data['reason'])) {
                $encounter->reason = $data['reason'];
                $encounter->save();
            }
        }

        $results = [];

        // Save allergies if provided
        if (!empty($data['allergies'])) {
            // Split allergies by comma or newline and save each one
            $allergies = preg_split('/[,\n]+/', $data['allergies']);
            foreach ($allergies as $allergen) {
                $allergen = trim($allergen);
                if ($allergen) {
                    // Check if this allergy already exists for this patient
                    $existingAllergy = Allergy::where('record_id', $recordId)
                        ->where('allergen', 'LIKE', '%' . $allergen . '%')
                        ->first();
                    
                    if (!$existingAllergy) {
                        $allergy = new Allergy();
                        $allergy->record_id = $recordId;
                        $allergy->allergen = $allergen;
                        $allergy->reaction = 'No especificada';
                        $allergy->severity = 'leve';
                        $allergy->recorded_by = $currentUserId;
                        $allergy->recorded_at = now();
                        $allergy->save();
                    }
                }
            }
            $results['allergies_saved'] = true;
        }

        // Save medical history - diagnosis goes in condition, motivo goes in details, antecedentes in medical_background
        $history = new MedicalHistory();
        $history->record_id = $recordId;
        $history->condition = $data['diagnosis']; // diagnóstico goes in condition
        $history->details = $data['reason'] ?? 'Consulta médica'; // motivo/observaciones goes in details
        $history->medical_background = $data['antecedentes'] ?? null; // antecedentes goes in medical_background
        $history->recorded_by = $currentUserId;
        $history->recorded_at = $encounterDate;
        $history->save();
        $results['history_saved'] = true;

        return response()->json([
            'success' => true,
            'encounter_id' => $encounter->id,
            'results' => $results
        ]);
    }

    public function getAllergies(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient || !$patient->record) return response()->json([], 200);

        $allergies = Allergy::where('record_id', $patient->record->id)
            ->orderByDesc('recorded_at')
            ->get()
            ->map(function($allergy) {
                return [
                    'id' => $allergy->id,
                    'allergen' => $allergy->allergen,
                    'reaction' => $allergy->reaction,
                    'severity' => $allergy->severity,
                    'recorded_at' => $allergy->recorded_at
                ];
            });

        return response()->json($allergies->toArray());
    }

    public function getMedicalHistory(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient || !$patient->record) return response()->json([], 200);

        $history = MedicalHistory::where('record_id', $patient->record->id)
            ->orderByDesc('recorded_at')
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'condition' => $record->condition,
                    'details' => $record->details,
                    'recorded_at' => $record->recorded_at
                ];
            });

        return response()->json($history->toArray());
    }

    // GET medico/api/diagnoses - Get all unique diagnoses for doctor's patients
    public function getDiagnoses(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        $medicoId = Auth::id();

        // Get all patient IDs that have appointments with this doctor
        $patientIds = Appointment::where('clinician_id', $medicoId)
            ->distinct()
            ->pluck('patient_id')
            ->toArray();

        if (empty($patientIds)) {
            return response()->json([]);
        }

        // Get medical records for these patients
        $recordIds = MedicalRecord::whereIn('patient_id', $patientIds)
            ->pluck('id')
            ->toArray();

        if (empty($recordIds)) {
            return response()->json([]);
        }

        // Get unique diagnoses (conditions) from medical histories
        $diagnoses = MedicalHistory::whereIn('record_id', $recordIds)
            ->whereNotNull('condition')
            ->where('condition', '!=', '')
            ->where('condition', '!=', 'Antecedentes')
            ->distinct()
            ->pluck('condition')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        return response()->json($diagnoses);
    }

    // GET medico/api/history-detail?history_id=XX&tipo=Historial
    public function historyDetail(Request $request)
    {
        $this->ensureMedicoOrAdmin();
        
        $historyId = $request->query('history_id');
        $tipo = $request->query('tipo');
        
        if (!$historyId || !$tipo) {
            return response()->json(['error' => 'history_id and tipo required'], 400);
        }
        
        $result = [];
        
        if ($tipo === 'Historial') {
            $history = MedicalHistory::find($historyId);
            if (!$history) {
                return response()->json(['error' => 'History not found'], 404);
            }
            
            $record = MedicalRecord::find($history->record_id);
            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }
            
            // Get the date for this history entry
            $historyDate = $history->recorded_at ?? $history->created_at;
            
            // Find encounters on the same date
            $encounters = Encounter::where('record_id', $record->id)
                ->whereDate('encounter_dt', substr($historyDate, 0, 10))
                ->get();
            
            // Get vital signs for these encounters
            if ($encounters->isNotEmpty()) {
                $encounterIds = $encounters->pluck('id')->toArray();
                $vitals = VitalSign::whereIn('encounter_id', $encounterIds)
                    ->orderBy('taken_at', 'desc')
                    ->first();
                
                if ($vitals) {
                    $result['vitals'] = [
                        'temperatura' => $vitals->temp_c ?? null,
                        'presion' => ($vitals->sbp && $vitals->dbp) ? $vitals->sbp . '/' . $vitals->dbp : null,
                        'pulso' => $vitals->hr ?? null,
                        'frecuencia_respiratoria' => $vitals->rr ?? null,
                        'spo2' => $vitals->spo2 ?? null,
                        'peso' => $vitals->weight_kg ?? null,
                        'altura' => $vitals->height_cm ?? null
                    ];
                }
            }
            
            // Get treatments for this patient around this date (within 30 days)
            $treatments = Treatment::where('record_id', $record->id)
                ->where(function($q) use ($historyDate) {
                    $q->whereDate('start_dt', '<=', substr($historyDate, 0, 10))
                      ->where(function($q2) use ($historyDate) {
                          $q2->whereNull('end_dt')
                             ->orWhereDate('end_dt', '>=', substr($historyDate, 0, 10));
                      });
                })
                ->get();
            
            if ($treatments->isNotEmpty()) {
                $result['tratamientos'] = $treatments->map(function($t) {
                    return ($t->name ?? '') . ' - ' . ($t->dose ?? '') . ' ' . ($t->frequency ?? '') . 
                           ($t->instructions ? ' (' . $t->instructions . ')' : '');
                })->toArray();
            }
        }
        
        return response()->json($result);
    }
}
