<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patient extends Model {
    use HasUuids;

    protected $fillable = [
        'user_id','curp','first_name','last_name','dob','sex','phone','email','address'
    ];

    public function record(){ return $this->hasOne(MedicalRecord::class); }
}
