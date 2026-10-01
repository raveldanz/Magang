<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Chat tahap lengkap: grup & Grup Bimbingan, balas/hapus pesan, banyak lampiran per pesan,
 * pin/mute percakapan, status online, dan tautan laporan pesan ke tiket Feedback.
 * Lampiran tahap 1 (kolom attachment_* di chat_messages) dipindahkan ke chat_attachments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('title', 150)->nullable();
            $table->string('description', 500)->nullable();
            // Grup Bimbingan otomatis: satu percakapan per penempatan
            $table->foreignId('placement_id')->nullable()->unique()->constrained('placements')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // Naik setiap nama/anggota berubah, agar browser tahu kapan memuat ulang detail grup
            $table->unsignedInteger('meta_version')->default(0);
        });

        Schema::table('chat_participants', function (Blueprint $table) {
            $table->string('role', 20)->default('member'); // admin | member
            $table->timestamp('muted_at')->nullable();
            $table->timestamp('pinned_at')->nullable();
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('type', 20)->default('text'); // text | system
            $table->unsignedBigInteger('reply_to_id')->nullable()->index();
            $table->json('meta')->nullable();
            // Pesan dihapus tetap tersimpan sebagai penanda "Pesan ini telah dihapus" (isi & lampiran dibuang)
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('chat_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->string('path');
            $table->string('name');
            $table->string('mime', 120);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('kind', 20); // image | video | audio | document
            $table->timestamps();

            $table->index('message_id');
        });

        DB::table('chat_messages')->whereNotNull('attachment_path')->chunkById(200, function ($rows) {
            DB::table('chat_attachments')->insert($rows->map(fn ($row) => [
                'message_id' => $row->id,
                'path' => $row->attachment_path,
                'name' => $row->attachment_name ?: 'lampiran',
                'mime' => $row->attachment_mime ?: 'application/octet-stream',
                'size' => (int) $row->attachment_size,
                'kind' => $row->attachment_kind ?: 'document',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ])->all());
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size', 'attachment_kind']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });

        Schema::table('system_feedbacks', function (Blueprint $table) {
            // Tiket "Laporan Pesan Chat" menunjuk pesan yang dilaporkan (untuk moderasi Super Admin)
            $table->unsignedBigInteger('chat_message_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('system_feedbacks', function (Blueprint $table) {
            $table->dropIndex(['chat_message_id']);
            $table->dropColumn('chat_message_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 120)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->string('attachment_kind', 20)->nullable();
        });

        // Tahap 1 hanya mendukung satu lampiran per pesan: kembalikan lampiran pertama
        foreach (DB::table('chat_attachments')->orderBy('id')->get()->groupBy('message_id') as $messageId => $attachments) {
            $first = $attachments->first();
            DB::table('chat_messages')->where('id', $messageId)->update([
                'attachment_path' => $first->path,
                'attachment_name' => $first->name,
                'attachment_mime' => $first->mime,
                'attachment_size' => $first->size,
                'attachment_kind' => $first->kind === 'audio' ? 'document' : $first->kind,
            ]);
        }

        Schema::dropIfExists('chat_attachments');

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['reply_to_id']);
            $table->dropColumn(['type', 'reply_to_id', 'meta', 'deleted_at', 'deleted_by']);
        });

        Schema::table('chat_participants', function (Blueprint $table) {
            $table->dropColumn(['role', 'muted_at', 'pinned_at']);
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['placement_id']);
                $table->dropForeign(['created_by']);
            }
            $table->dropUnique(['placement_id']);
            $table->dropColumn(['title', 'description', 'placement_id', 'created_by', 'meta_version']);
        });
    }
};
