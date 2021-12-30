<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Booking extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $fillable = [
        'from_date',
        'to_date',
        'user_id',
        'room_id'
    ];
}
