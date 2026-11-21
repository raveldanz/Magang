<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus saluran pengumuman kampus/sekolah yang tidak pernah dipakai.
 *
 * ChatChannelService versi lama membuat satu saluran untuk SETIAP institusi setiap kali /chat dibuka. Setelah
 * master sekolah disinkron dari direktori nasional (puluhan ribu sekolah), hal ini menimbun ribuan saluran kosong.
 * Yang dihapus hanya saluran institusi tanpa pengguna DAN tanpa pesan; saluran yang dipakai tetap utuh, dan
 * saluran baru dibuat otomatis begitu institusi itu punya pengguna yang membuka chat.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_conversations') || ! Schema::hasTable('chat_participants')) {
            return;
        }

        $empty = fn (): Builder => DB::table('chat_conversations')
            ->select('chat_conversations.id')
            ->where('chat_conversations.type', 'channel')
            ->where('chat_conversations.scope_type', 'university')
            ->whereNotNull('chat_conversations.university_id')
            ->whereNotIn('chat_conversations.university_id', DB::table('users')->whereNotNull('university_id')->select('university_id'))
            ->whereNotExists(fn (Builder $q) => $q->select(DB::raw(1))->from('chat_messages')->whereColumn('chat_messages.conversation_id', 'chat_conversations.id'));

        do {
            $ids = $empty()->orderBy('chat_conversations.id')->limit(1000)->pluck('id');
            if ($ids->isEmpty()) {
                break;
            }
            DB::transaction(function () use ($ids) {
                DB::table('chat_participants')->whereIn('conversation_id', $ids)->delete();
                DB::table('chat_conversations')->whereIn('id', $ids)->delete();
            });
        } while ($ids->count() === 1000);
    }

    public function down(): void
    {
        // Saluran kosong tidak perlu dikembalikan; ChatChannelService membuatnya lagi bila dibutuhkan.
    }
};
