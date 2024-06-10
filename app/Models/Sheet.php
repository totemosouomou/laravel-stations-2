<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'column',
        'row',
        'screen_id',
    ];

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}