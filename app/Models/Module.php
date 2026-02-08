<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'course_id',
        'title',
    ];

    protected $appends = [
        'total_lessons',
        'completed_lessons',
        'progress_percentage',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function getTotalLessonsAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getCompletedLessonsAttribute(): int
    {
        return $this->lessons()->where('is_completed', true)->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        return round(($this->completed_lessons / $this->total_lessons) * 100, 2);
    }
}

