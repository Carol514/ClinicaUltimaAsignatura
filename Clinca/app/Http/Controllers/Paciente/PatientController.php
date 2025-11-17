<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Document;
use App\Models\Treatment;
use App\Models\MedicalHistory;
use App\Models\VitalSign;
use App\Models\Reminder;

class PatientController extends Controller
{
    // Return unified history events for a patient
    public function history(Request $request)
    {
        $patient = $this->resolvePatient($request);
        if (!$patient) return response()->json([], 200);

        $record = $patient->record;
        $items = [];

        // Appointments (patient-level)
        $appts = Appointment::where('patient_id', $patient->id)->get();
        foreach ($appts as $a) {
            $dt = $a->scheduled_at ? substr($a->scheduled_at,0,10) : ($a->created_at?substr($a->created_at,0,10):null);
            $items[] = [
                'fecha' => $dt,
                'tipo' => 'Cita',
                'detalle' => trim(($a->reason ?? '') . ' (' . ($a->status ?? '') . ')'),
            ];
        }

        if ($record) {
            $rid = $record->id;

            $enc = Encounter::where('record_id', $rid)->get();
            foreach ($enc as $e){
                $dt = $e->encounter_dt ? substr($e->encounter_dt,0,10) : ($e->created_at?substr($e->created_at,0,10):null);
                $items[] = ['fecha'=>$dt,'tipo'=>'Encuentro','detalle'=>($e->reason ?? '') . ' ' . ($e->notes ?? '')];
            }

            $mh = MedicalHistory::where('record_id', $rid)->get();
            foreach ($mh as $m){
                $dt = $m->recorded_at ? substr($m->recorded_at,0,10) : ($m->created_at?substr($m->created_at,0,10):null);
                $items[] = ['fecha'=>$dt,'tipo'=>'Historial','detalle'=>($m->condition ?? '') . ' - ' . ($m->details ?? '')];
            }

            $docs = Document::where('record_id', $rid)->get();
            foreach ($docs as $d){
                $dt = $d->created_at ? substr($d->created_at,0,10) : null;
                $items[] = ['fecha'=>$dt,'tipo'=>'Documento','detalle'=>($d->title ?? $d->doc_type ?? '')];
            }

            $treats = Treatment::where('record_id', $rid)->get();
            foreach ($treats as $t){
                $dt = $t->start_dt ? substr($t->start_dt,0,10) : ($t->created_at?substr($t->created_at,0,10):null);
                $items[] = ['fecha'=>$dt,'tipo'=>'Tratamiento','detalle'=>($t->name ?? '') . ' ' . ($t->dose ?? '')];
            }

            $vitals = VitalSign::where('encounter_id', $enc->pluck('id')->toArray())->get();
            foreach ($vitals as $v){
                $dt = $v->taken_at ? substr($v->taken_at,0,10) : ($v->created_at?substr($v->created_at,0,10):null);
                $items[] = ['fecha'=>$dt,'tipo'=>'Signos vitales','detalle'=>('TA ' . ($v->sbp ?? '') . '/' . ($v->dbp ?? '') . ' · Temp ' . ($v->temp_c ?? ''))];
            }
        }

        // sort by fecha desc
        usort($items, function($a,$b){ return strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''); });

        return response()->json(array_values($items));
    }

    // Return reminders/citas for a patient
    public function reminders(Request $request)
    {
        $patient = $this->resolvePatient($request);
        if (!$patient) return response()->json([], 200);

        $appts = Appointment::where('patient_id', $patient->id)->get();
        $rows = [];
        foreach ($appts as $a){
            $fecha = $a->scheduled_at ? substr($a->scheduled_at,0,10) : null;
            $hora  = $a->scheduled_at ? substr($a->scheduled_at,11,5) : null;
            $estado = $a->status ?? 'Programada';

            // look for Reminder entries
            $rem = Reminder::where('appointment_id', $a->id)->orderBy('send_at')->get();
            if ($rem && $rem->count()){
                foreach ($rem as $r){
                    $rows[] = [
                        'fecha' => $r->send_at ? substr($r->send_at,0,10) : $fecha,
                        'hora'  => $r->send_at ? substr($r->send_at,11,5) : $hora,
                        'tipo'  => 'Recordatorio',
                        'detalle'=> $r->channel . ' ' . ($r->result ?? ''),
                        'estado' => $r->sent ? 'Enviado' : 'Pendiente',
                    ];
                }
            } else {
                $rows[] = [
                    'fecha'=>$fecha,'hora'=>$hora,'tipo'=>'Cita','detalle'=>($a->reason ?? ''),'estado'=>$estado
                ];
            }
        }

        usort($rows, function($a,$b){ return strcmp($a['fecha'].$a['hora'], $b['fecha'].$b['hora']); });
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
