<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'cycle_id',
        'student_id',
        'amount_cents',
        'method',
        'status',
        'bank_reference',
        'submitted_at',
        'paid_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_note',
        'paid_note',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PaymentAttachment::class);
    }
}
