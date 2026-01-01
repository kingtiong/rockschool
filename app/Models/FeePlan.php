<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeePlan extends Model
{
    /** @use HasFactory<\Database\Factories\FeePlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cycle_fee_cents',
        'lessons_per_cycle',
        'minutes_per_lesson_default',
        'allow_half_hour',
        'active',
    ];

    protected $casts = [
        'allow_half_hour' => 'boolean',
        'active' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
