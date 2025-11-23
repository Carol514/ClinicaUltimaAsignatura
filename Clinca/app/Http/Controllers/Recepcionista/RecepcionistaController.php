<?php
namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class RecepcionistaController extends Controller {

    // Return a simple list of patients
    public function listPatients(Request $request){
        $q = trim((string)$request->query('q',''));
        $query = Patient::query()->select(['id','curp','first_name','last_name','phone','email','address']);
        if ($q !== ''){
            $query->where(function($w) use ($q){
                $w->where('curp', 'like', "%{$q}%")
                  ->orWhere(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $items = $query->limit(200)->get()->map(function($p){
            return [
                'id' => $p->id,
                'curp' => $p->curp,
                'nombre' => trim($p->first_name . ' ' . $p->last_name),
                'phone' => $p->phone,
                'email' => $p->email,
                'address' => $p->address,
            ];
        });
        return response()->json(['data'=>$items]);
    }

    // Search endpoint (alias)
    public function searchPatients(Request $request){
        return $this->listPatients($request);
    }

    // Return list of medicos (users with role 'medico')
    public function listMedicos(Request $request){
        $rows = DB::table('users')
            ->join('users_roles','users_roles.user_id','=','users.id')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('roles.code','medico')
            ->select('users.id','users.name')
            ->distinct()
            ->orderBy('users.name','asc')
            ->get();

        $out = $rows->map(function($r){ 
            // Extract last name from full name (assuming format: "FirstName LastName")
            $nameParts = explode(' ', trim($r->name));
            $lastName = count($nameParts) > 1 ? end($nameParts) : $r->name;
            return ['id'=>$r->id,'name'=>'Dr. ' . $lastName]; 
        });
        return response()->json(['data'=>$out]);
    }

    // Get single patient
    public function getPatient(Request $request, $id){
        $p = Patient::find($id);
        if (!$p) return response()->json(['error'=>'Paciente no encontrado'], 404);
        return response()->json(['data'=>[ 'id'=>$p->id, 'curp'=>$p->curp, 'first_name'=>$p->first_name, 'last_name'=>$p->last_name, 'dob'=>$p->dob, 'sex'=>$p->sex, 'phone'=>$p->phone, 'email'=>$p->email, 'address'=>$p->address ]]);
    }

    // Create patient
    public function storePatient(Request $request){
        // Normalize input and map human-friendly sex values to DB enum (M,F,I)
        $data = $request->only(['curp','first_name','last_name','dob','sex','phone','email','address','age']);

        // Convert age to date of birth if age is provided instead of dob
        if (!empty($data['age']) && empty($data['dob'])) {
            $age = (int)$data['age'];
            if ($age > 0 && $age <= 120) {
                // Calculate approximate birth year (current year - age)
                $birthYear = now()->year - $age;
                // Use January 1st as approximate birth date
                $data['dob'] = $birthYear . '-01-01';
            }
            unset($data['age']); // Remove age field as it's not stored in DB
        }

        // map common spanish labels to enum values expected by DB
        if (!empty($data['sex'])){
            $s = trim((string)$data['sex']);
            $sUpper = strtoupper(substr($s,0,1));
            if (in_array($sUpper, ['M','F','I'])){
                $data['sex'] = $sUpper;
            } else {
                $lower = strtolower($s);
                if (str_contains($lower, 'mas')) $data['sex'] = 'M';
                elseif (str_contains($lower, 'fem')) $data['sex'] = 'F';
                else $data['sex'] = 'I';
            }
        } else {
            // remove sex so DB default applies
            unset($data['sex']);
        }

        $validated = validator($data, [
            'first_name' => 'required|string|max:120',
            'last_name'  => 'nullable|string|max:120',
            'curp'       => 'nullable|string|max:32',
            'dob'        => 'nullable|date',
            'sex'        => 'nullable|in:M,F,I',
            'phone'      => 'nullable|string|max:40',
            'email'      => 'nullable|email|max:200',
            'address'    => 'nullable|string|max:400',
            'age'        => 'nullable|integer|min:0|max:120', // Accept age for conversion to dob
        ])->validate();

        $patient = Patient::create($validated);

        $createdUser = null;
        // If an email was provided and there is no existing user with that email, create a User account
        if (!empty($validated['email'])){
            $existing = User::where('email', $validated['email'])->first();
            if ($existing){
                // link to existing user account
                $patient->user_id = $existing->id;
                $patient->save();
                $createdUser = ['id'=>$existing->id,'email'=>$existing->email,'linked'=>true];
            } elseif (!$existing){
                $tempPassword = Str::random(10);
                $user = User::create([
                    'name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')) ?: ($validated['first_name'] ?? 'Paciente'),
                    'email' => $validated['email'],
                    'password' => $tempPassword,
                ]);

                // attach paciente role in pivot
                $role = Role::where('code', 'paciente')->orWhere('name','paciente')->first();
                if ($role){
                    DB::table('users_roles')->insert(['user_id'=>$user->id,'role_id'=>$role->id]);
                }

                // link patient to user
                $patient->user_id = $user->id;
                $patient->save();

                $createdUser = ['id'=>$user->id,'email'=>$user->email,'temp_password'=>$tempPassword];
            }
        }

        $out = ['data'=>$patient];
        if ($createdUser) $out['created_user'] = $createdUser;
        return response()->json($out, 201);
    }

    // Update patient
    public function updatePatient(Request $request, $id){
        $p = Patient::find($id);
        if (!$p) return response()->json(['error'=>'Paciente no encontrado'], 404);
        $data = $request->only(['curp','first_name','last_name','dob','sex','phone','email','address']);

        if (!empty($data['sex'])){
            $s = trim((string)$data['sex']);
            $sUpper = strtoupper(substr($s,0,1));
            if (in_array($sUpper, ['M','F','I'])){
                $data['sex'] = $sUpper;
            } else {
                $lower = strtolower($s);
                if (str_contains($lower, 'mas')) $data['sex'] = 'M';
                elseif (str_contains($lower, 'fem')) $data['sex'] = 'F';
                else $data['sex'] = 'I';
            }
        } else {
            unset($data['sex']);
        }

        $validated = validator($data, [
            'first_name' => 'sometimes|required|string|max:120',
            'last_name'  => 'sometimes|required|string|max:120',
            'curp'       => 'nullable|string|max:32',
            'dob'        => 'nullable|date',
            'sex'        => 'nullable|in:M,F,I',
            'phone'      => 'nullable|string|max:40',
            'email'      => 'nullable|email|max:200',
            'address'    => 'nullable|string|max:400',
        ])->validate();
        $p->fill($validated);
        $p->save();
        return response()->json(['data'=>$p]);
    }

    // Appointments list
    public function listAppointments(Request $request){
        $from = $request->query('from');
        $to   = $request->query('to');
        $q    = trim((string)$request->query('q',''));
        $clinicianId = $request->query('clinician_id');

        $query = Appointment::query()->select(['id','patient_id','scheduled_at','duration_min','reason','status','clinician_id']);
        if ($from) $query->where('scheduled_at','>=', $from . ' 00:00:00');
        if ($to) $query->where('scheduled_at','<=', $to . ' 23:59:59');
        if ($clinicianId) $query->where('clinician_id', $clinicianId);
        if ($q) $query->where(function($w) use ($q){
            $w->where('reason','like','%'.$q.'%')
              ->orWhereHas('patient', function($p) use ($q) {
                  $p->where('first_name','like','%'.$q.'%')
                    ->orWhere('last_name','like','%'.$q.'%');
              });
        });
        $apps = $query->orderBy('scheduled_at','asc')->limit(500)->get();

        // Collect patient & clinician ids to resolve names in batch
        $patientIds = $apps->pluck('patient_id')->filter()->unique()->values()->all();
        $clinicianIds = $apps->pluck('clinician_id')->filter()->unique()->values()->all();

        $patients = [];
        if (count($patientIds)){
            $patients = Patient::whereIn('id', $patientIds)->get()->mapWithKeys(function($p){
                return [$p->id => trim($p->first_name . ' ' . $p->last_name)];
            })->toArray();
        }

        $clinicians = [];
        if (count($clinicianIds)){
            $rows = DB::table('users')->whereIn('id', $clinicianIds)->select('id','name')->get();
            $clinicians = $rows->mapWithKeys(function($r){
                // Extract last name from full name (assuming format: "FirstName LastName")
                $nameParts = explode(' ', trim($r->name));
                $lastName = count($nameParts) > 1 ? end($nameParts) : $r->name;
                return [$r->id => 'Dr. ' . $lastName];
            })->toArray();
        }

        $items = $apps->map(function($a) use ($patients, $clinicians){
            return [
                'id'=>$a->id,
                'patient_id'=>$a->patient_id,
                'patient_name'=> $patients[$a->patient_id] ?? null,
                'scheduled_at'=>$a->scheduled_at,
                'duration_min'=>$a->duration_min,
                'reason'=>$a->reason,
                'status'=>$a->status,
                'clinician_id'=>$a->clinician_id,
                'clinician_name'=> $clinicians[$a->clinician_id] ?? null,
            ];
        });

        return response()->json(['data'=>$items]);
    }

    public function storeAppointment(Request $request){
        $validated = $request->validate([
            'patient_id' => 'required|string|exists:patients,id',
            'scheduled_at' => 'required|date',
            'duration_min' => 'nullable|integer',
            'reason' => 'nullable|string|max:400',
            'clinician_id' => 'nullable|string|exists:users,id',
        ]);
            // use DB enum values (lowercase) to avoid enum truncation warnings
        // include created_by to track who created the appointment
        $app = Appointment::create(array_merge($validated, [
            'status' => 'programada',
            'created_by' => Auth::id()
        ]));
        
        // Send notification email if patient has opted in
        $this->sendAppointmentNotification($app, 'created');
        
        return response()->json(['data'=>$app], 201);
    }

    public function updateAppointment(Request $request, $id){
        $a = Appointment::find($id);
        if (!$a) return response()->json(['error'=>'Cita no encontrada'], 404);
        
        $oldStatus = $a->status;
        
        $validated = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'duration_min' => 'nullable|integer',
            'reason' => 'nullable|string|max:400',
            'status' => 'nullable|in:programada,confirmada,no_asistio,cancelada,atendida',
            'clinician_id' => 'nullable|string|exists:users,id',
        ]);
        $a->fill($validated);
        $a->save();
        
        // Send appropriate notification email based on status change
        $newStatus = $a->status;
        if (isset($validated['status']) && $oldStatus !== $newStatus) {
            // Status changed - send specific notification
            if ($newStatus === 'cancelada') {
                $this->sendAppointmentNotification($a, 'cancelled');
            } elseif ($newStatus === 'no_asistio') {
                $this->sendAppointmentNotification($a, 'no_show');
            } elseif ($newStatus === 'atendida') {
                $this->sendAppointmentNotification($a, 'completed');
            } else {
                $this->sendAppointmentNotification($a, 'updated');
            }
        } elseif (isset($validated['scheduled_at']) || isset($validated['reason'])) {
            // Date/time or reason changed but not status
            $this->sendAppointmentNotification($a, 'updated');
        }
        
        return response()->json(['data'=>$a]);
    }

    public function deleteAppointment(Request $request, $id){
        $a = Appointment::find($id);
        if (!$a) return response()->json(['error'=>'Cita no encontrada'], 404);
        $a->delete();
        return response()->json(['data'=>['id'=>$id]]);
    }

    // Send appointment notification email if patient has opted in
    private function sendAppointmentNotification($appointment, $type)
    {
        try {
            Log::info('Attempting to send appointment notification', [
                'appointment_id' => $appointment->id,
                'type' => $type
            ]);

            $patient = $appointment->patient;
            if (!$patient || !$patient->email) {
                Log::info('No patient or email found', [
                    'patient' => $patient ? $patient->id : 'null',
                    'email' => $patient ? $patient->email : 'null'
                ]);
                return;
            }

            // Check if patient has opted in for email notifications
            $key = 'patient_notifications_' . $patient->id;
            $prefs = Cache::get($key);
            Log::info('Patient notification preferences', [
                'patient_id' => $patient->id,
                'cache_key' => $key,
                'preferences' => $prefs
            ]);
            
            if (!$prefs || !$prefs['enabled'] || !$prefs['email']) {
                Log::info('Patient has not opted in for email notifications');
                return;
            }

            $scheduledAt = $appointment->scheduled_at ? \Carbon\Carbon::parse($appointment->scheduled_at) : null;
            $dateStr = $scheduledAt ? $scheduledAt->format('d/m/Y') : 'Fecha por confirmar';
            $timeStr = $scheduledAt ? $scheduledAt->format('H:i') : 'Hora por confirmar';
            
            // Set subject and message based on notification type
            $subject = '';
            $message = "Estimado/a {$patient->first_name} {$patient->last_name},\n\n";
            
            switch ($type) {
                case 'created':
                    $subject = 'Cita Programada';
                    $message .= "Su cita ha sido programada para el {$dateStr} a las {$timeStr}.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo: {$appointment->reason}\n";
                    }
                    $message .= "\nPor favor, llegue 15 minutos antes de su cita.\n\nSaludos,\nClínica";
                    break;
                    
                case 'updated':
                    $subject = 'Cita Actualizada';
                    $message .= "Su cita ha sido actualizada para el {$dateStr} a las {$timeStr}.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo: {$appointment->reason}\n";
                    }
                    $message .= "\nPor favor, llegue 15 minutos antes de su cita.\n\nSaludos,\nClínica";
                    break;
                    
                case 'cancelled':
                    $subject = 'Cita Cancelada';
                    $message .= "Le informamos que su cita programada para el {$dateStr} a las {$timeStr} ha sido cancelada.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo original: {$appointment->reason}\n";
                    }
                    $message .= "\nSi necesita reagendar, por favor contacte con la clínica.\n\nSaludos,\nClínica";
                    break;
                    
                case 'no_show':
                    $subject = 'Cita - No Asistió';
                    $message .= "Le informamos que no asistió a su cita programada para el {$dateStr} a las {$timeStr}.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo: {$appointment->reason}\n";
                    }
                    $message .= "\nSi desea reagendar o tiene alguna consulta, por favor contacte con la clínica.\n\nSaludos,\nClínica";
                    break;
                    
                case 'completed':
                    $subject = 'Cita Completada';
                    $message .= "Su cita del {$dateStr} a las {$timeStr} ha sido completada exitosamente.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo: {$appointment->reason}\n";
                    }
                    $message .= "\nGracias por su visita.\n\nSaludos,\nClínica";
                    break;
                    
                default:
                    $subject = 'Actualización de Cita';
                    $message .= "Ha habido una actualización en su cita para el {$dateStr} a las {$timeStr}.\n";
                    if ($appointment->reason) {
                        $message .= "Motivo: {$appointment->reason}\n";
                    }
                    $message .= "\nSaludos,\nClínica";
            }

            // Use Laravel's Mail facade to send email
            Log::info('Sending appointment email', [
                'to' => $patient->email,
                'subject' => $subject
            ]);

            Mail::raw($message, function ($mail) use ($patient, $subject) {
                $mail->to($patient->email)
                     ->subject($subject);
            });

            Log::info('Appointment email sent successfully', [
                'to' => $patient->email,
                'type' => $type
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send appointment notification', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id ?? 'unknown'
            ]);
        }
    }
}
