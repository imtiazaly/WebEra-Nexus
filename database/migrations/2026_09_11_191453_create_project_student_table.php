<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('Intern Developer'); // e.g. Frontend Developer, UI Designer, SEO Specialist
            $table->unsignedInteger('progress')->default(0); // 0 to 100 %
            $table->string('submission_status')->default('assigned'); // assigned, in_progress, submitted, reviewed, approved, revision_needed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_student');
    }
};
