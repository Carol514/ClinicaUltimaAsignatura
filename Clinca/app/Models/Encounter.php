<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Encounter extends Model {
    use HasUuids;
    protected $fillable = ['record_id','encounter_dt','reason','notes','clinician_id'];
}
