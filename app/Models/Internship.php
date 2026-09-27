<?php

namespace App\Models;

use Database\Factories\InternshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Internship extends Model
{
    /** @use HasFactory<InternshipFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'name',
        'batch_no',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'internship_student')
            ->withPivot('id', 'status', 'progress', 'grade', 'certificate_code', 'joined_at', 'completed_at', 'notes')
            ->withTimestamps();
    }
}
