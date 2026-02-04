<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'requested_clock_in',
        'requested_clock_out',
        'requested_break_start',
        'requested_break_end',
        'reason',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'requested_break_start' => 'datetime',
        'requested_break_end' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
