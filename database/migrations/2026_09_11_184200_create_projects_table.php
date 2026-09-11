<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // client_id NULLABLE hai: NULL matlab Company Internal Practice Project
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('planning'); // planning, in_progress, on_hold, under_review, completed, cancelled
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->unsignedInteger('progress')->default(0); // 0 to 100 %
            $table->text('ai_status_summary')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
