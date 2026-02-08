<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'platform_id',
        'title',
    ];

    protected $appends = [
        'total_lessons',
        'completed_lessons',
        'progress_percentage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function getTotalLessonsAttribute(): int
    {
        return Lesson::whereHas('module', fn (Builder $query) => $query->where('course_id', $this->id))->count();
    }

    public function getCompletedLessonsAttribute(): int
    {
        return Lesson::whereHas('module', fn (Builder $query) => $query->where('course_id', $this->id))->where('is_completed', true)->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        return round(($this->completed_lessons / $this->total_lessons) * 100, 2);
    }
}

