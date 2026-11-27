<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 20); // manual | schedule
            $table->string('status', 20)->index(); // queued | running | success | failed
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('forms');
            $table->unsignedInteger('per_page');
            $table->unsignedInteger('max_pages')->default(0);
            $table->string('current_step', 20)->nullable();
            $table->unsignedInteger('current_page')->default(0);
            $table->unsignedInteger('last_page')->nullable();
            $table->unsignedBigInteger('source_total')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('proposed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('failed_steps')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('institution_change_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->nullable()->constrained('universities')->cascadeOnDelete();
            $table->string('kind', 20); // name | address | new_college
            $table->string('current_value', 500)->nullable();
            $table->string('proposed_value', 500);
            $table->json('payload')->nullable();
            $table->char('fingerprint', 40)->unique();
            $table->string('status', 20)->default('pending')->index(); // pending | accepted | rejected
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->timestamp('directory_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn('directory_synced_at');
        });
        Schema::dropIfExists('institution_change_proposals');
        Schema::dropIfExists('institution_sync_runs');
    }
};
