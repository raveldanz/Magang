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
        Schema::create('academic_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_advisor_id')->constrained('users')->onDelete('cascade');
            $table->date('consultation_date');
            $table->string('topic');
            $table->text('notes');
            $table->string('stage')->default('Bimbingan Laporan');
            $table->string('status')->default('completed');
            $table->timestamps();

            $table->index(['placement_id', 'consultation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_consultations');
    }
};
