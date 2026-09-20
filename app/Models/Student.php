<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\StudentFactory;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internship_id',
        'name',
        'email',
        'phone',
        'status',
        'overall_progress',
    ];

    protected $casts = [
        'overall_progress' => 'integer',
    ];

    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class)->latest('week_number');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_student')
            ->withPivot('id', 'role', 'progress', 'submission_status')
            ->withTimestamps();
    }
}
