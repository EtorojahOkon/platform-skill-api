<?php

namespace App\Models;

use App\Enums\Proficiency;
use App\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assessment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'assessable_id',
        'assessable_type',
        'ai_comments',
        'rating',
        'proficiency',
        'status',
    ];

    protected $casts = [
        'ai_comments' => 'array',
        'status' => AssessmentStatus::class,
        'proficiency' => Proficiency::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessable(): MorphTo
    {
        return $this->morphTo();
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'assessable_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'assessable_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'assessable_id');
    }
}
