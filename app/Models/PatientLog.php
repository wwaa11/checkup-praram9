<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientLog extends Model
{
    //
    protected $table = 'patient_logs';

    protected $fillable = [
        'patient',
        'detail',
        'user',
    ];
}
