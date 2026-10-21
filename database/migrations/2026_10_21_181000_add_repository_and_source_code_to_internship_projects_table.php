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
            $table->string('repository_url', 500)->nullable()->after('demo_url');
            $table->string('source_code_path')->nullable()->after('repository_url');
            $table->string('source_code_name')->nullable()->after('source_code_path');
            $table->string('source_code_size')->nullable()->after('source_code_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_projects', function (Blueprint $table) {
            $table->dropColumn([
                'repository_url',
                'source_code_path',
                'source_code_name',
                'source_code_size',
            ]);
        });
    }
};
