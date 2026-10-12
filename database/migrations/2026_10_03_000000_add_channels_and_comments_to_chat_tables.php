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
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('scope_type', 20)->nullable()->index(); // government | university
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete();
            $table->foreignId('agency_profile_id')->nullable()->constrained('agency_profiles')->nullOnDelete();
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_broadcast_only')->default(false)->index();

            $table->index(['type', 'scope_type']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('chat_messages')->cascadeOnDelete();
            $table->unsignedInteger('comments_count')->default(0);

            $table->index(['conversation_id', 'parent_id']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['parent_id']);
            }
            $table->dropIndex(['conversation_id', 'parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['parent_id', 'comments_count']);
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['university_id']);
                $table->dropForeign(['agency_profile_id']);
            }
            $table->dropIndex(['type', 'scope_type']);
            $table->dropIndex(['scope_type']);
            $table->dropIndex(['is_broadcast_only']);
            $table->dropColumn(['scope_type', 'university_id', 'agency_profile_id', 'image_url', 'is_broadcast_only']);
        });
    }
};
