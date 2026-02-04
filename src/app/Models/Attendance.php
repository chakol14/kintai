<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'break_minutes',
        'break_started_at',
        'status',
        'remark',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'break_started_at' => 'datetime',
    ];

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }
}
