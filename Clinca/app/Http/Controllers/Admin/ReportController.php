<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $type = $request->input('type');
        $from = $request->input('from');
        $to = $request->input('to');

        if ($type === 'usuarios') {
            $rows = DB::table('roles')
                ->leftJoin('users_roles', 'roles.id', '=', 'users_roles.role_id')
                ->leftJoin('users', 'users_roles.user_id', '=', 'users.id')
                ->select('roles.name as role', DB::raw('count(users.id) as count'))
                ->groupBy('roles.name')
                ->get();
            return response()->json(['encabezado' => ['Rol', 'Cantidad'], 'datos' => $rows]);
        }

        if ($type === 'citas') {
            // If appointments table exists, group by date
            if (SchemaHasTable('appointments')) {
                $rows = DB::table('appointments')
                    ->select(DB::raw('date(scheduled_at) as fecha'), DB::raw('count(*) as cantidad'))
                    ->groupBy(DB::raw('date(scheduled_at)'))
                    ->orderBy('fecha')
                    ->get();
                return response()->json(['encabezado' => ['Fecha', 'Citas programadas'], 'datos' => $rows]);
            }
            // fallback
            return response()->json(['encabezado' => ['Fecha', 'Citas programadas'], 'datos' => []]);
        }

        if ($type === 'tratamientos') {
            if (SchemaHasTable('treatments')) {
                $rows = DB::table('treatments')
                    ->select('name', DB::raw('count(*) as aplicaciones'))
                    ->groupBy('name')
                    ->get();
                return response()->json(['encabezado' => ['Tratamiento', 'Aplicaciones'], 'datos' => $rows]);
            }
            return response()->json(['encabezado' => ['Tratamiento', 'Aplicaciones'], 'datos' => []]);
        }

        return response()->json(['message' => 'Tipo de reporte no soportado'], 400);
    }
}

/**
 * Helper to check if a table exists without importing Schema in the class header.
 */
function SchemaHasTable(string $name): bool {
    return DB::getSchemaBuilder()->hasTable($name);
}
