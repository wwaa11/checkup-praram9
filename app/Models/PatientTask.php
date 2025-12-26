<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTask extends Model
{
    // Status List
    // wait => default create
    // queue => default create
    // working => serving in counter
    // hold => holding
    // finish => finish get VN

    // list of station code and name
    const STATION_LIST = [
        '01'  => 'วัดความดัน',
        '011' => 'ห้องเจาะเลือด',
        '012' => 'ตรวจตา',
        '013' => 'ตรวจทันตกรรม',
        '02'  => 'EKG',
        '03'  => 'ABI',
        '04'  => 'EST',
        '05'  => 'Echo',
        '06'  => 'Xray',
        '07'  => 'US',
        '08'  => 'Mammogram',
        '09'  => 'BoneDense',
        '10'  => 'OBS',
        '11'  => 'CT UpperGI',
        '12'  => 'MRI',
        '13'  => 'หมอตา',
        '14'  => 'หมอหู',
        '15'  => 'PFT เป่าปอด',
        '16'  => 'หมอกระดูก',
        '17'  => 'ATK',
        '18'  => 'แพทย์ตรวจสุขภาพ',
        '19'  => 'คูปองอาหาร',
        '20'  => 'FibroScan',
    ];

    protected $table = 'patient_tasks';

    protected $fillable = [
        'patient',
        'date',
        'code',
        'status',
    ];

    public function patient_master()
    {
        return $this->belongsTo(PatientMaster::class, 'patient', 'id');
    }
}
