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
        if (!Schema::hasTable('system_feedbacks')) {
            Schema::create('system_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('sender_name');
                $table->string('sender_email');
                $table->string('sender_role')->default('mahasiswa');
                $table->string('target_role')->default('super_admin'); // super_admin, admin_dinas, universitas
                $table->foreignId('target_agency_id')->nullable()->constrained('agency_profiles')->nullOnDelete();
                $table->foreignId('target_university_id')->nullable()->constrained('universities')->nullOnDelete();
                $table->string('category')->default('error_bug'); // error_bug, saran_fitur, pertanyaan, koordinasi, lainnya
                $table->string('subject');
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->string('status')->default('pending'); // pending, in_progress, resolved, closed
                $table->text('admin_response')->nullable();
                $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('system_notifications')) {
            Schema::create('system_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('target_role')->nullable(); // null means all or specific user_id
                $table->string('type')->default('info'); // info, warning, success, urgent, action_required
                $table->string('category')->default('system'); // university, application, logbook, feedback, evaluation
                $table->string('icon')->default('🔔');
                $table->string('title');
                $table->text('message');
                $table->string('action_url')->nullable();
                $table->string('action_label')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('system_feedbacks');
    }
};
