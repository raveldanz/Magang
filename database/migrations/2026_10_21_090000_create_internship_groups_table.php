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
        Schema::create('internship_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name')->nullable();
            $table->foreignId('leader_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft, pending, waiting_approval, accepted, active, completed, rejected
            $table->string('proposal_letter_path')->nullable();
            $table->string('letter_number')->nullable();
            $table->date('letter_date')->nullable();
            $table->string('letter_token', 64)->nullable()->unique();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('leader_user_id');
            $table->index('unit_id');
            $table->index('status');
            $table->index('letter_token');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('unit_id')->constrained('internship_groups')->nullOnDelete();
            $table->string('role_in_group', 20)->nullable()->after('group_id'); // 'leader', 'member'

            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id', 'role_in_group']);
        });

        Schema::dropIfExists('internship_groups');
    }
};
