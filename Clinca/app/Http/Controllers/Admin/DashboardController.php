<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        // Get patient role IDs
        $patientRoleIds = DB::table('roles')
            ->whereIn('code', ['paciente', 'patient'])
            ->orWhereIn('name', ['Paciente', 'Patient'])
            ->pluck('id')
            ->toArray();

        // Count total patients
        $totalPacientes = 0;
        if (!empty($patientRoleIds)) {
            $totalPacientes = DB::table('users_roles')
                ->whereIn('role_id', $patientRoleIds)
                ->distinct('user_id')
                ->count('user_id');
        }

        // Count today's appointments
        $today = Carbon::today()->toDateString();
        $citasHoy = Appointment::whereDate('scheduled_at', $today)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->count();

        // Count active staff (non-patient users)
        $totalPersonal = 0;
        if (!empty($patientRoleIds)) {
            $patientUserIds = DB::table('users_roles')
                ->whereIn('role_id', $patientRoleIds)
                ->pluck('user_id')
                ->toArray();
            
            $totalPersonal = User::whereNotIn('id', $patientUserIds)->count();
        } else {
            $totalPersonal = User::count();
        }

        return response()->json([
            'pacientes' => $totalPacientes,
            'citas_hoy' => $citasHoy,
            'personal' => $totalPersonal
        ]);
    }

    public function appointmentsByStatus()
    {
        $stats = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $statusMap = [
            'pending' => 'Programadas',
            'confirmed' => 'Confirmadas',
            'completed' => 'Atendidas',
            'no_show' => 'No asistió',
            'cancelled' => 'Canceladas'
        ];

        $result = [];
        $total = 0;

        foreach ($stats as $stat) {
            $label = $statusMap[$stat->status] ?? $stat->status;
            $result[] = [
                'status' => $stat->status,
                'label' => $label,
                'count' => $stat->total
            ];
            $total += $stat->total;
        }

        // Calculate percentages
        foreach ($result as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100) : 0;
        }

        return response()->json([
            'data' => $result,
            'total' => $total
        ]);
    }

    public function appointmentsLast7Days()
    {
        $days = [];
        $dayLabels = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Appointment::whereDate('scheduled_at', $date->toDateString())
                ->where('status', 'completed')
                ->count();
            
            $days[] = [
                'label' => $dayLabels[$date->dayOfWeek],
                'value' => $count,
                'date' => $date->format('Y-m-d')
            ];
        }

        return response()->json($days);
    }
}
