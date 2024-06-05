<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'schedule_id',
        'sheet_id',
        'email',
        'name',
        'is_canceled',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }
}
