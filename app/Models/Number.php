<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    protected $table    = 'number_dates';
    protected $fillable = [
        'date',
    ];
}
