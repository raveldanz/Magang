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
        Schema::create('internship_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('placements')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('community_benefit');
            $table->string('photo_path');
            $table->string('demo_url')->nullable();
            $table->timestamps();

            $table->index('placement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_projects');
    }
};
