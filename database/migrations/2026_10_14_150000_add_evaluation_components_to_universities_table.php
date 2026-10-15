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
            if (! Schema::hasColumn('universities', 'dinas_components')) {
                $table->json('dinas_components')->nullable()->after('require_dpl');
            }
            if (! Schema::hasColumn('universities', 'dpl_components')) {
                $table->json('dpl_components')->nullable()->after('dinas_components');
            }

            $table->string('evaluation_scheme', 32)->default('mentor_only')->change();
            $table->unsignedTinyInteger('weight_mentor')->default(100)->change();
            $table->unsignedTinyInteger('weight_lecturer')->default(0)->change();
            $table->boolean('require_dpl')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'dinas_components')) {
                $table->dropColumn('dinas_components');
            }
            if (Schema::hasColumn('universities', 'dpl_components')) {
                $table->dropColumn('dpl_components');
            }

            $table->string('evaluation_scheme', 32)->default('dual_evaluation')->change();
            $table->unsignedTinyInteger('weight_mentor')->default(40)->change();
            $table->unsignedTinyInteger('weight_lecturer')->default(60)->change();
            $table->boolean('require_dpl')->default(true)->change();
        });
    }
};
