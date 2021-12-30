<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Price extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'start_range',
        'end_range',
        'value',
        'room_id'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
