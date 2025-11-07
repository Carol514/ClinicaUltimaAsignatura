<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model {
  use HasUuids;
  protected $fillable = ['record_id','encounter_id','doc_type','title','storage_uri','uploaded_by'];
}
