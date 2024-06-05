<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'start_time',
        'end_time',
    ];

    protected $dates = [
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'start_time_date' => 'datetime',
        'start_time_time' => 'datetime',
        'end_time' => 'datetime',
        'end_time_date' => 'datetime',
        'end_time_time' => 'datetime',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}