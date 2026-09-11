<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('week_number');
            $table->text('tasks_completed');
            $table->text('learnings')->nullable();
            $table->text('blockers')->nullable();
            $table->string('status')->default('submitted'); // submitted, under_review, approved, revision_requested
            $table->text('feedback')->nullable();
            $table->text('ai_summary')->nullable(); // AI summary column
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
