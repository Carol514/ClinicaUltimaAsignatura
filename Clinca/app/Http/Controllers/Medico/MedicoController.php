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

    // Search patients by name or id (used by medico panel)
    public function searchPatients(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $q = $request->query('query');
        if (!$q) return response()->json([], 200);

        $query = Patient::query();
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

    // GET medico/api/history?patient_id=XX
    public function historial(Request $request)
    {
        $this->ensureMedicoOrAdmin();

        $pid = $request->query('patient_id') ?? $request->input('patient_id');
        if (!$pid) return response()->json(['error'=>'patient_id requerido'], 400);

        $patient = Patient::find($pid);
        if (!$patient) return response()->json([], 200);

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
            $items[] = ['fecha'=>$dt,'tipo'=>'Cita','detalle'=>trim(($a->reason ?? '') . ' (' . ($a->status ?? '') . ')'),'autor'=>$autor];
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
                $items[] = ['fecha'=>$dt,'tipo'=>'Encuentro','detalle'=>($e->reason ?? '') . ' ' . ($e->notes ?? ''),'autor'=>$autor];
            }

            $mh = MedicalHistory::where('record_id', $rid)->get();
            foreach ($mh as $m){
                $dt = $m->recorded_at ? substr($m->recorded_at,0,10) : ($m->created_at?substr($m->created_at,0,10):null);
                $autor = null;
                if ($m->recorded_by) {
                    try { $autor = DB::table('users')->where('id', $m->recorded_by)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = ['fecha'=>$dt,'tipo'=>'Historial','detalle'=>($m->condition ?? '') . ' - ' . ($m->details ?? ''),'autor'=>$autor];
            }

            $docs = Document::where('record_id', $rid)->get();
            foreach ($docs as $d){
                $dt = $d->created_at ? substr($d->created_at,0,10) : null;
                $autor = null;
                if ($d->uploaded_by) {
                    try { $autor = DB::table('users')->where('id', $d->uploaded_by)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = ['fecha'=>$dt,'tipo'=>'Documento','detalle'=>($d->title ?? $d->doc_type ?? ''),'autor'=>$autor];
            }

            $treats = Treatment::where('record_id', $rid)->get();
            foreach ($treats as $t){
                $dt = $t->start_dt ? substr($t->start_dt,0,10) : ($t->created_at?substr($t->created_at,0,10):null);
                $autor = null;
                if ($t->updated_by) {
                    try { $autor = DB::table('users')->where('id', $t->updated_by)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = ['fecha'=>$dt,'tipo'=>'Tratamiento','detalle'=>($t->name ?? '') . ' ' . ($t->dose ?? ''),'autor'=>$autor];
            }

            $vitals = VitalSign::whereIn('encounter_id', $enc->pluck('id')->toArray())->get();
            foreach ($vitals as $v){
                $dt = $v->taken_at ? substr($v->taken_at,0,10) : ($v->created_at?substr($v->created_at,0,10):null);
                $autor = null;
                if ($v->nurse_id) {
                    try { $autor = DB::table('users')->where('id', $v->nurse_id)->value('name'); } catch(\Throwable $e) { }
                }
                $items[] = ['fecha'=>$dt,'tipo'=>'Signos vitales','detalle'=>('TA ' . ($v->sbp ?? '') . '/' . ($v->dbp ?? '') . ' · Temp ' . ($v->temp_c ?? '')),'autor'=>$autor];
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
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string', // antecedentes
            'vitals' => 'nullable|array',
            'vitals.temp' => 'nullable|numeric',
            'vitals.sbp' => 'nullable|integer',
            'vitals.dbp' => 'nullable|integer',
            'vitals.hr' => 'nullable|integer',
            'vitals.rr' => 'nullable|integer',
            'vitals.spo2' => 'nullable|integer',
            'vitals.weight' => 'nullable|numeric'
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
            $encounter->reason = $data['reason'] ?? 'Alta de historial';
            $encounter->doctor_id = $currentUserId;
            $encounter->save();
        }

        $results = [];

        // Save vital signs if provided
        if (!empty($data['vitals'])) {
            $vitals = $data['vitals'];
            if (array_filter($vitals)) { // Only save if at least one vital sign has a value
                // Try to find existing vital signs for this encounter/date
                $vitalSign = VitalSign::where('encounter_id', $encounter->id)
                    ->whereDate('taken_at', $encounterDate)
                    ->first();
                
                // If no existing vital sign found, create new one
                if (!$vitalSign) {
                    $vitalSign = new VitalSign();
                    $vitalSign->encounter_id = $encounter->id;
                    $vitalSign->taken_at = $encounterDate;
                    $vitalSign->nurse_id = $currentUserId; // Doctor recording vitals
                }
                
                // Update/set the vital sign values
                $vitalSign->temp_c = $vitals['temp'] ?? $vitalSign->temp_c;
                $vitalSign->sbp = $vitals['sbp'] ?? $vitalSign->sbp;
                $vitalSign->dbp = $vitals['dbp'] ?? $vitalSign->dbp;
                $vitalSign->hr = $vitals['hr'] ?? $vitalSign->hr;
                $vitalSign->rr = $vitals['rr'] ?? $vitalSign->rr;
                $vitalSign->spo2 = $vitals['spo2'] ?? $vitalSign->spo2;
                $vitalSign->weight_kg = $vitals['weight'] ?? $vitalSign->weight_kg;
                
                $vitalSign->save();
                $results['vitals_updated'] = !$vitalSign->wasRecentlyCreated;
                $results['vitals_saved'] = true;
            }
        }

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
                        $allergy->severity = 'leve'; // Use valid ENUM value
                        $allergy->recorded_by = $currentUserId;
                        $allergy->recorded_at = now();
                        $allergy->save();
                    }
                }
            }
            $results['allergies_saved'] = true;
        }

        // Save medical history (motivo/observaciones) if provided
        if (!empty($data['reason'])) {
            $history = new MedicalHistory();
            $history->record_id = $recordId;
            $history->condition = $data['reason']; // motivo/observaciones goes in condition
            $history->details = $data['medical_history'] ?? null; // antecedentes goes in details
            $history->recorded_by = $currentUserId;
            $history->recorded_at = now();
            $history->save();
            $results['history_saved'] = true;
        }
        
        // Save antecedentes separately if provided and not already saved with reason
        if (!empty($data['medical_history']) && empty($data['reason'])) {
            // Check if this antecedente already exists to avoid duplicates
            $existingHistory = MedicalHistory::where('record_id', $recordId)
                ->where('details', 'LIKE', '%' . trim($data['medical_history']) . '%')
                ->where('condition', 'Antecedentes')
                ->first();
                
            if (!$existingHistory) {
                $history = new MedicalHistory();
                $history->record_id = $recordId;
                $history->condition = 'Antecedentes';
                $history->details = $data['medical_history'];
                $history->recorded_by = $currentUserId;
                $history->recorded_at = now();
                $history->save();
                $results['antecedentes_saved'] = true;
            }
        }

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
}
