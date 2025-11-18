<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Appointment extends Model {
  use HasUuids;
  protected $fillable = ['patient_id','scheduled_at','duration_min','reason','status','created_by','clinician_id'];

  /**
   * Get the patient that owns the appointment.
   */
  public function patient()
  {
      return $this->belongsTo(Patient::class);
  }
}
