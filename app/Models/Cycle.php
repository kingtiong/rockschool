<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cycle extends Model
{
    /** @use HasFactory<\Database\Factories\CycleFactory> */
    use HasFactory;

    public const STATUS_AWAITING_STUDENT_PAYMENT = 'awaiting_student_payment';
    public const STATUS_PAYMENT_SUBMITTED = 'payment_submitted';
    public const STATUS_PAID = 'paid';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_AWAITING_STUDENT_PAYMENT,
        self::STATUS_PAYMENT_SUBMITTED,
        self::STATUS_PAID,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'enrollment_id',
        'cycle_fee_cents',
        'lessons_per_cycle',
        'minutes_per_lesson',
        'interval_weeks',
        'cycle_minutes_total',
        'cycle_number',
        'status',
        'starts_on',
    ];

    protected $casts = [
        'starts_on' => 'date',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function additionalCharges(): HasMany
    {
        return $this->hasMany(AdditionalCharge::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function additionalChargesTotalCents(): int
    {
        if ($this->relationLoaded('additionalCharges')) {
            return (int) $this->additionalCharges->sum('amount_cents');
        }

        return (int) $this->additionalCharges()->sum('amount_cents');
    }

    public function totalDueCents(): int
    {
        return (int) $this->cycle_fee_cents + $this->additionalChargesTotalCents();
    }
}
