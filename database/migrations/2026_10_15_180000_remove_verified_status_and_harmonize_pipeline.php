<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('applications')) {
            // Migrasikan data yang masih berstatus 'verified'
            // Jika sudah ada mentor yang diplot di placement, alihkan ke 'waiting_approval', jika belum ke 'pending'
            $verifiedApps = DB::table('applications')->where('status', 'verified')->get();

            foreach ($verifiedApps as $app) {
                $hasMentor = DB::table('placements')
                    ->where('application_id', $app->id)
                    ->where(function ($q) {
                        $q->whereNotNull('mentor_id')
                            ->orWhereNotNull('pembimbing_id');
                    })
                    ->exists();

                $newStatus = $hasMentor ? 'waiting_approval' : 'pending';

                DB::table('applications')
                    ->where('id', $app->id)
                    ->update(['status' => $newStatus]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Status 'verified' dihapus permanen dari sistem
    }
};
