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
        Schema::table('internship_projects', function (Blueprint $table) {
            $table->json('screenshots')->nullable()->after('photo_path');
            $table->json('documents')->nullable()->after('screenshots');
            $table->json('community_photos')->nullable()->after('community_benefit');
            $table->string('video_url')->nullable()->after('demo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_projects', function (Blueprint $table) {
            $table->dropColumn(['screenshots', 'documents', 'community_photos', 'video_url']);
        });
    }
};
