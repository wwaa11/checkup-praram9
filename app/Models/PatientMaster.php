<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMaster extends Model
{
    //
    protected $table = 'patient_masters';

    protected $fillable = [
        'hn',
        'firstname',
        'lastname',
        'nationality',
        'nationalid',
        'passport',
        'phoneno',
    ];

    public function getTasks()
    {
        return $this->hasMany(PatientTask::class, 'patient', 'id');
    }

    public function getPreVN()
    {
        return $this->hasOne(PatientPrevN::class, 'patient', 'id');
    }

    public function getLogs()
    {
        return $this->hasMany(PatientLog::class, 'patient', 'id');
    }
}
