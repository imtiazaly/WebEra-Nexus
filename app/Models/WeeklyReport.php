<?php

namespace App\Models;

use Database\Factories\WeeklyReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    /** @use HasFactory<WeeklyReportFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'week_number',
        'tasks_completed',
        'learnings',
        'blockers',
        'status',
        'feedback',
        'ai_summary',
        'submitted_at',
    ];

    protected $casts = [
        'week_number' => 'integer',
        'submitted_at' => 'datetime',
    ];

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
