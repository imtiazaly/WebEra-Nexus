<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'service_id',
        'title',
        'description',
        'status',
        'start_date',
        'deadline',
        'progress',
        'ai_status_summary',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'deadline' => 'date:Y-m-d',
        'progress' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'project_student')
            ->withPivot('id', 'role', 'progress', 'submission_status')
            ->withTimestamps();
    }
}
