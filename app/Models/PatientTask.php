<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTask extends Model
{
    //
    protected $table = 'patient_tasks';

    protected $fillable = [
        'patient',
        'code',
    ];
}
