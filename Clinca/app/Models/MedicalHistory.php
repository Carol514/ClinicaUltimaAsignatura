<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MedicalHistory extends Model {
  use HasUuids;
  protected $fillable = ['record_id','condition','details','medical_background','recorded_by','recorded_at'];
}
