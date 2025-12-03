<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
