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
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'evaluation_scheme')) {
                $table->string('evaluation_scheme', 32)->default('dual_evaluation')->after('logo');
            }
            if (!Schema::hasColumn('universities', 'weight_mentor')) {
                $table->unsignedTinyInteger('weight_mentor')->default(40)->after('evaluation_scheme');
            }
            if (!Schema::hasColumn('universities', 'weight_lecturer')) {
                $table->unsignedTinyInteger('weight_lecturer')->default(60)->after('weight_mentor');
            }
            if (!Schema::hasColumn('universities', 'require_dpl')) {
                $table->boolean('require_dpl')->default(true)->after('weight_lecturer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn(['evaluation_scheme', 'weight_mentor', 'weight_lecturer', 'require_dpl']);
        });
    }
};
