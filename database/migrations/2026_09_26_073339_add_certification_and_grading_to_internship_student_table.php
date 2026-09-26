<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('internship_student', function (Blueprint $table) {
            $table->string('grade')->nullable()->after('progress');
            $table->string('certificate_code')->nullable()->after('grade');
            $table->text('notes')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_student', function (Blueprint $table) {
            $table->dropColumn(['grade', 'certificate_code', 'notes']);
        });
    }
};
