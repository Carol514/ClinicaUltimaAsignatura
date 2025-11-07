<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MedicalRecord extends Model {
    use HasUuids;

    protected $fillable = ['patient_id','status'];

    public function patient(){ return $this->belongsTo(Patient::class); }
}
