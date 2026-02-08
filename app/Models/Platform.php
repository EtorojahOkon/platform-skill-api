<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use App\Enums\Proficiency;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Platform extends Model
{
    /** @use HasFactory<\Database\Factories\ProficiencySessionFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'name',
        'proficiency',
    ];

    protected $casts = [
        'proficiency' => Proficiency::class,
    ];

    public function course(): HasOne
    {
        return $this->hasOne(Course::class);
    }

    public function assessments(): MorphMany
    {
        return $this->morphMany(Assessment::class, 'assessable');
    }
}
