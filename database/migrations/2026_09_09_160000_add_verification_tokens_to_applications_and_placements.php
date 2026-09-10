<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom letter_token pada tabel applications
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'letter_token')) {
                $table->string('letter_token', 64)->nullable()->unique()->after('letter_date');
            }
        });

        // 2. Tambahkan kolom certificate_number dan certificate_hash pada tabel placements
        Schema::table('placements', function (Blueprint $table) {
            if (!Schema::hasColumn('placements', 'certificate_number')) {
                $table->string('certificate_number', 100)->nullable()->after('status');
            }
            if (!Schema::hasColumn('placements', 'certificate_hash')) {
                $table->string('certificate_hash', 64)->nullable()->unique()->after('certificate_number');
            }
        });

        // 3. Backfill data eksisting agar tidak NULL
        $year = date('Y');
        $applications = DB::table('applications')->whereNull('letter_token')->get();
        foreach ($applications as $app) {
            $updateData = [
                'letter_token' => Str::random(32),
            ];
            if (empty($app->letter_number) && strtolower($app->status) === 'accepted') {
                $paddedId = str_pad($app->id, 3, '0', STR_PAD_LEFT);
                $updateData['letter_number'] = "500.12.1/{$paddedId}/436.7.14/{$year}";
                $updateData['letter_date'] = $app->letter_date ?? date('Y-m-d');
            }
            DB::table('applications')->where('id', $app->id)->update($updateData);
        }

        $placements = DB::table('placements')->whereNull('certificate_hash')->get();
        foreach ($placements as $plc) {
            $paddedId = str_pad($plc->id, 3, '0', STR_PAD_LEFT);
            DB::table('placements')->where('id', $plc->id)->update([
                'certificate_hash' => Str::random(32),
                'certificate_number' => "SERT/{$paddedId}/PEMKOT-SBY/{$year}",
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'letter_token')) {
                $table->dropColumn('letter_token');
            }
        });

        Schema::table('placements', function (Blueprint $table) {
            if (Schema::hasColumn('placements', 'certificate_hash')) {
                $table->dropColumn('certificate_hash');
            }
            if (Schema::hasColumn('placements', 'certificate_number')) {
                $table->dropColumn('certificate_number');
            }
        });
    }
};
