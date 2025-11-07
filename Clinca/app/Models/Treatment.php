<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Treatment extends Model {
  use HasUuids;
  protected $fillable = ['encounter_id','record_id','name','dose','route','start_dt','end_dt','status','updated_by'];
}
