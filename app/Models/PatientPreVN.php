<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPreVN extends Model
{
    // Status List
    // wait => default create
    // walkin => change default to walkin
    // followup => change default to followup
    // working => serving in counter
    // hold => holding
    // finish => finish get VN

    protected $table = 'patient_prevns';

    protected $fillable = [
        'patient',
        'date',
        'type',
        'checkin',
        'status',
    ];

    protected $appends = ['priority'];

    protected $casts = [
        'checkin' => 'datetime',
    ];

    public function patient_master()
    {
        return $this->belongsTo(PatientMaster::class, 'patient', 'id');
    }

    public function getPriorityAttribute()
    {
        switch ($this->type) {
            case 'U':
                return 1;
            case 'A':
                return 2;
            case 'B':
                return 3;
            case 'C':
                return 4;
            case 'D':
                return 5;
            case 'E':
                return 6;
            case 'H':
                return 7;
            case 'V':
                return 8;
            case 'M':
                return 9;
            default:
                return 99;
        }
    }
}
