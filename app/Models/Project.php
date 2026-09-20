<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\ProjectFactory;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
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

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'project_student')
            ->withPivot('id', 'role', 'progress', 'submission_status')
            ->withTimestamps();
    }
}
