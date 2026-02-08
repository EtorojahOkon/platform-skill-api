<?php

namespace App\Models;

use App\Enums\GoalType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Goal extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'title',
        'type',
        'target'
    ];

    protected $casts = [
        'type' => GoalType::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_completed', 'completed_at');
    }
}
