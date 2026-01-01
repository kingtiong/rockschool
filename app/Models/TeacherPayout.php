<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherPayout extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherPayoutFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'teacher_id',
        'amount_cents',
        'status',
        'paid_at',
        'reference',
        'created_by_user_id',
        'note',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(TeacherEarning::class);
    }
}
