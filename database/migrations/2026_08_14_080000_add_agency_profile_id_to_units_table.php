<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('agency_profile_id')->nullable()->after('id')->constrained('agency_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['agency_profile_id']);
            $table->dropColumn('agency_profile_id');
        });
    }
};
