<?php

namespace App\Http\Controllers\Enfermera;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\MedicalRecord;
use App\Models\VitalSign;

class EnfermeraController extends Controller
{
    protected function ensureEnfermeraOrAdmin()
    {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);

        if (!in_array($role, ['enfermera', 'administrador'])) {
            abort(403, 'Acceso no autorizado');
        }
    }

    // Search patients similar to medico panel
    public function searchPatients(Request $request)
    {
        $this->ensureEnfermeraOrAdmin();
        $q = $request->query('query');
        if (!$q) return response()->json([], 200);

        $query = Patient::query();
        // Accept numeric ids or UUIDs
        $isUuid = preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $q);
        if (is_numeric($q) || $isUuid) {
            $query->where('id', $q);
        } else {
            $qClean = trim($q);
            $terms = preg_split('/\s+/', $qClean);

            $query->where(function($qq) use ($qClean, $terms) {
                $qq->where('first_name', 'like', "%{$qClean}%")
                   ->orWhere('last_name', 'like', "%{$qClean}%")
                   ->orWhere('email', 'like', "%{$qClean}%")
                   ->orWhere('curp', 'like', "%{$qClean}%")
                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$qClean}%"])
                   ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$qClean}%"]);

                if (count($terms) >= 2) {
                    $first = $terms[0];
                    $last = $terms[count($terms)-1];
                    $qq->orWhere(function($q2) use ($first, $last){
                        $q2->where('first_name', 'like', "%{$first}%")->where('last_name', 'like', "%{$last}%");
                    });
                    $qq->orWhere(function($q3) use ($first, $last){
                        $q3->where('first_name', 'like', "%{$last}%")->where('last_name', 'like', "%{$first}%");
                    });
                }
            });
        }

        $list = $query->limit(10)->get()->map(function($p){
            $age = null;
            if (!empty($p->dob)) {
                try { $age = \Carbon\Carbon::parse($p->dob)->age . ' años'; } catch(\Throwable $e) { $age = null; }
            }
            $lastAppt = Appointment::where('patient_id', $p->id)->orderByDesc('scheduled_at')->value('scheduled_at');
            $lastEnc = null;
            if ($p->record && $p->record->id) {
                $lastEnc = Encounter::where('record_id', $p->record->id)->orderByDesc('encounter_dt')->value('encounter_dt');
            }
            $last = $lastAppt ?: $lastEnc ?: null;
            $fullName = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
            return [
                'id' => $p->id,
                'name' => $fullName ?: null,
                'gender' => $p->sex ?? null,
                'age' => $age,
                'last_consult' => $last ? substr($last,0,10) : null,
            ];
        });

        return response()->json($list);
    }

    // GET vitals for a patient (latest N)
    public function vitals(Request $request)
    {
        $this->ensureEnfermeraOrAdmin();
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
                'spo2' => $s->spo2,
                'nurse_id' => $s->nurse_id,
                'nurse_name' => $s->nurse_id && isset($nurseMap[$s->nurse_id]) ? $nurseMap[$s->nurse_id] : null,
            ];
        });

        // $out is a Collection; convert to array before returning
        return response()->json($out->values()->all());
    }

    // POST add a vital sign (create encounter if none)
    public function storeVital(Request $request)
    {
        $this->ensureEnfermeraOrAdmin();

        $data = $request->validate([
            // patients use UUIDs in this app; accept string or numeric ids
            'patient_id' => 'required',
            'taken_at' => 'nullable|date',
            'temp' => 'nullable|numeric',
            'ta' => 'required|string',
            'pulso' => 'nullable|integer',
            'fr' => 'nullable|integer',
            'spo2' => 'nullable|integer',
        ]);

        $patient = Patient::find($data['patient_id']);
        if (!$patient) return response()->json(['error'=>'patient not found'], 404);
        $recordId = $patient->record ? $patient->record->id : null;
        if (!$recordId) {
            // create a minimal medical record for the patient so we can attach encounters/vitals
            $mr = MedicalRecord::create(['patient_id'=>$patient->id, 'status'=>'activo']);
            $recordId = $mr->id;
        }

        // create a new encounter for this vital if needed
        $enc = Encounter::create([ 'record_id'=>$recordId, 'encounter_dt'=>($data['taken_at'] ?? now()), 'reason'=>'Signos vitales', 'notes'=>null ]);

        // parse TA (sbp/dbp)
        $sbp = null; $dbp = null;
        if (isset($data['ta']) && preg_match('/(\d{2,3})\D+(\d{2,3})/', $data['ta'], $m)){
            $sbp = intval($m[1]); $dbp = intval($m[2]);
        }

        $vs = VitalSign::create([
            'encounter_id' => $enc->id,
            'taken_at' => $data['taken_at'] ?? now(),
            'temp_c' => $data['temp'] ?? null,
            'sbp' => $sbp,
            'dbp' => $dbp,
            'hr'  => $data['pulso'] ?? null,
            'rr'  => $data['fr'] ?? null,
            'spo2'=> $data['spo2'] ?? null,
            'nurse_id' => Auth::id(),
        ]);

        return response()->json(['created'=>true,'id'=>$vs->id]);
    }
}
