<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Reminder extends Model {
  use HasUuids;
  protected $fillable = ['appointment_id','channel','send_at','sent','result'];
}
