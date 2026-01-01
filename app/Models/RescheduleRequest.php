<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescheduleRequest extends Model
{
    /** @use HasFactory<\Database\Factories\RescheduleRequestFactory> */
    use HasFactory;

    public const TYPE_CHANGE = 'change';
    public const TYPE_ABSENCE = 'absence';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_AUTO_APPLIED = 'auto_applied';

    protected $fillable = [
        'lesson_id',
        'requested_by_user_id',
        'type',
        'requested_start_at',
        'reason',
        'status',
        'decided_by_user_id',
        'decided_at',
        'decision_note',
    ];

    protected $casts = [
        'requested_start_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
