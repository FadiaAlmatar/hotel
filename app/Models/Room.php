<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'room_number',
        'count_beds',
        'base_price',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class,'bookings');
    }
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

}
