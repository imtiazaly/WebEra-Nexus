<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internship_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('enrolled'); // enrolled, active, completed, dropped_out
            $table->unsignedInteger('progress')->default(0); // 0 to 100% per batch
            $table->date('joined_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });

        // Optional: Make old internship_id on students table nullable
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('internship_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_student');
    }
};
