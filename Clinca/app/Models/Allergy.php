<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Allergy extends Model {
  use HasUuids;
  protected $fillable = ['record_id','allergen','reaction','severity','recorded_by','recorded_at'];
}
