<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEarning extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherEarningFactory> */
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'lesson_id',
        'teacher_id',
        'teacher_payout_id',
        'amount_cents',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(TeacherPayout::class, 'teacher_payout_id');
    }
}
