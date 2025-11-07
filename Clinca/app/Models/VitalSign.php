<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VitalSign extends Model {
    use HasUuids;
    protected $fillable = [
        'encounter_id','taken_at','temp_c','sbp','dbp','hr','rr','spo2','weight_kg','height_cm','nurse_id'
    ];
}
