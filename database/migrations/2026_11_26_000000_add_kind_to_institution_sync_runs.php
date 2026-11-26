<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_sync_runs', function (Blueprint $table) {
            $table->string('kind', 12)->default('school')->index();
        });
    }

    public function down(): void
    {
        Schema::table('institution_sync_runs', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropColumn('kind');
        });
    }
};
