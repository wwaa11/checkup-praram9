<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'station_id',
        'name',
        'doctor',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
