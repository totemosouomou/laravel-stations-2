<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function sheets()
    {
        return $this->hasMany(Sheet::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
