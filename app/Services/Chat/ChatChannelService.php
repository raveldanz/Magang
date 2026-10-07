<?php

namespace App\Services\Chat;

use App\Models\AgencyProfile;
use App\Models\ChatConversation;
use App\Models\ChatParticipant;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengelolaan Saluran Pengumuman Resmi (Tingkat Dinas Kota dan Universitas).
 */
class ChatChannelService
{
    /**
     * Memastikan seluruh Saluran Pengumuman Resmi terdaftar di sistem:
     * 1. Saluran Pengumuman Pemerintah Kota Surabaya
     * 2. Saluran Pengumuman [Nama Lembaga Dinas]
     * 3. Saluran Pengumuman [Nama Lembaga Kampus]
     */
    public function ensureOfficialChannels(): void
    {
        if (! Schema::hasTable('chat_conversations')) {
            return;
        }

        // 1. Saluran Utama Pemkot Surabaya
        ChatConversation::updateOrCreate(
            [
                'type' => ChatConversation::TYPE_CHANNEL,
                'scope_type' => ChatConversation::SCOPE_GOVERNMENT,
                'agency_profile_id' => null,
                'university_id' => null,
            ],
            [
                'title' => 'Pemerintah Kota Surabaya',
                'description' => 'Saluran siaran pengumuman resmi program magang Pemerintah Kota Surabaya.',
                'image_url' => is_file(public_path('images/logos/surabaya.png')) ? 'images/logos/surabaya.png' : 'images/logoPemkotSBY.png',
                'is_broadcast_only' => true,
            ]
        );

        // 2. Saluran Pengumuman tiap Dinas Kota
        if (Schema::hasTable('agency_profiles')) {
            $agencies = AgencyProfile::all();
            foreach ($agencies as $agency) {
                ChatConversation::updateOrCreate(
                    [
                        'type' => ChatConversation::TYPE_CHANNEL,
                        'scope_type' => ChatConversation::SCOPE_GOVERNMENT,
                        'agency_profile_id' => $agency->id,
                    ],
                    [
                        'title' => $agency->agency_name,
                        'description' => "Saluran siaran pengumuman resmi {$agency->agency_name}.",
                        'image_url' => $agency->logo ?: (is_file(public_path('images/logos/surabaya.png')) ? 'images/logos/surabaya.png' : 'images/logoPemkotSBY.png'),
                        'is_broadcast_only' => true,
                    ]
                );
            }
        }

        // 3. Saluran Pengumuman tiap Universitas
        if (Schema::hasTable('universities')) {
            $universities = University::all();
            foreach ($universities as $univ) {
                ChatConversation::updateOrCreate(
                    [
                        'type' => ChatConversation::TYPE_CHANNEL,
                        'scope_type' => ChatConversation::SCOPE_UNIVERSITY,
                        'university_id' => $univ->id,
                    ],
                    [
                        'title' => $univ->name,
                        'description' => "Saluran siaran pengumuman resmi {$univ->name}.",
                        'image_url' => $univ->logo ?: 'images/default-university.svg',
                        'is_broadcast_only' => true,
                    ]
                );
            }
        }
    }

    /**
     * Mendaftarkan pengguna secara otomatis ke saluran pengumuman yang sesuai:
     * - Semua pengguna otomatis tergabung ke Saluran Pengumuman Pemkot Surabaya.
     * - Mahasiswa, Dosen, dan Admin Kampus otomatis tergabung ke Saluran Kampus masing-masing.
     * - Admin Dinas dan Mentor otomatis tergabung ke Saluran Dinas masing-masing.
     * - Mahasiswa yang memiliki penempatan magang aktif di dinas juga otomatis tergabung ke Saluran Dinas terkait.
     */
    public function ensureChannelsForUser(User $user): void
    {
        if (! Schema::hasTable('chat_conversations') || ! Schema::hasTable('chat_participants')) {
            return;
        }

        $this->ensureOfficialChannels();

        // Ambil ID saluran yang wajib diikuti user
        $channelIds = collect();

        // Saluran Pemkot Surabaya (semua user wajib tergabung)
        $pemkotChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('scope_type', ChatConversation::SCOPE_GOVERNMENT)
            ->whereNull('agency_profile_id')
            ->value('id');
        if ($pemkotChannel) {
            $channelIds->push($pemkotChannel);
        }

        // Saluran Kampus (jika user terafiliasi dengan universitas)
        if ($user->university_id) {
            $univChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
                ->where('scope_type', ChatConversation::SCOPE_UNIVERSITY)
                ->where('university_id', $user->university_id)
                ->value('id');
            if ($univChannel) {
                $channelIds->push($univChannel);
            }
        }

        // Saluran Dinas (jika user terafiliasi dengan dinas)
        if ($user->agency_profile_id) {
            $agencyChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
                ->where('scope_type', ChatConversation::SCOPE_GOVERNMENT)
                ->where('agency_profile_id', $user->agency_profile_id)
                ->value('id');
            if ($agencyChannel) {
                $channelIds->push($agencyChannel);
            }
        }

        // Jika mahasiswa memiliki penempatan magang di unit dinas
        if ($user->role === 'mahasiswa') {
            $agencyIds = DB::table('applications')
                ->join('units', 'units.id', '=', 'applications.unit_id')
                ->where('applications.user_id', $user->id)
                ->whereIn('applications.status', ['accepted', 'active', 'completed'])
                ->pluck('units.agency_profile_id')
                ->filter()
                ->unique();

            if ($agencyIds->isNotEmpty()) {
                $placedAgencyChannels = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
                    ->where('scope_type', ChatConversation::SCOPE_GOVERNMENT)
                    ->whereIn('agency_profile_id', $agencyIds)
                    ->pluck('id');
                $channelIds = $channelIds->merge($placedAgencyChannels);
            }
        }

        // Jika Super Admin, gabungkan ke semua saluran agar dapat memantau & memoderasi
        if ($user->isSuperAdmin()) {
            $allChannelIds = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)->pluck('id');
            $channelIds = $channelIds->merge($allChannelIds);
        }

        $channelIds = $channelIds->unique()->values();
        if ($channelIds->isEmpty()) {
            return;
        }

        $existing = ChatParticipant::where('user_id', $user->id)
            ->whereIn('conversation_id', $channelIds)
            ->pluck('conversation_id')
            ->all();

        $missing = $channelIds->diff($existing);
        if ($missing->isEmpty()) {
            return;
        }

        $channels = ChatConversation::whereIn('id', $missing)->get();

        foreach ($channels as $channel) {
            $role = $this->canManageChannel($channel, $user)
                ? ChatParticipant::ROLE_ADMIN
                : ChatParticipant::ROLE_MEMBER;

            ChatParticipant::firstOrCreate(
                [
                    'conversation_id' => $channel->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $role,
                    'last_read_message_id' => $channel->last_message_id,
                ]
            );
        }
    }

    /**
     * Memeriksa apakah pengguna berhak menjadi Admin/Pengelola pada saluran terkait.
     */
    public function canManageChannel(ChatConversation $channel, User $user): bool
    {
        if ($channel->type !== ChatConversation::TYPE_CHANNEL) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($channel->scope_type === ChatConversation::SCOPE_GOVERNMENT) {
            if ($channel->agency_profile_id === null) {
                return $user->isSuperAdmin();
            }

            return $user->role === 'admin' && (int) $user->agency_profile_id === (int) $channel->agency_profile_id;
        }

        if ($channel->scope_type === ChatConversation::SCOPE_UNIVERSITY) {
            return in_array($user->role, ['universitas', 'admin'], true)
                && (int) $user->university_id === (int) $channel->university_id;
        }

        return false;
    }

    /**
     * Hak mempublikasikan pengumuman utama (parent_id == null).
     * Hanya Admin Dinas dan Admin Kampus yang berhak posting di feed utama.
     */
    public function canPublishAnnouncement(ChatConversation $channel, User $user): bool
    {
        return $this->canManageChannel($channel, $user);
    }

    /**
     * URL logo resmi saluran (Gambar/PNG logo Dinas atau Kampus yang valid).
     */
    public function channelLogoUrl(ChatConversation $channel): string
    {
        // 1. Jika terhubung ke Dinas / OPD, ambil logo resmi dari Profil Dinas
        if ($channel->agency_profile_id && $channel->agencyProfile) {
            return $channel->agencyProfile->logo_url;
        }

        // 2. Jika terhubung ke Universitas / Kampus, ambil logo resmi dari Profil Kampus
        if ($channel->university_id && $channel->university) {
            return $channel->university->logo_url;
        }

        // 3. Jika Saluran Utama Pemerintah Kota Surabaya
        if ($channel->scope_type === ChatConversation::SCOPE_GOVERNMENT && $channel->agency_profile_id === null) {
            if (is_file(public_path('images/logos/surabaya.png'))) {
                return asset('images/logos/surabaya.png');
            }
            if (is_file(public_path('images/logoPemkotSBY.png'))) {
                return asset('images/logoPemkotSBY.png');
            }
        }

        // 4. Logo kustom eksplisit yang bukan placeholder generik
        $logo = ltrim((string) $channel->image_url, '/');
        if ($logo !== '' && ! in_array($logo, ['images/default-university.svg', 'images/default-agency.svg'], true)) {
            if (is_file(public_path($logo))) {
                return asset($logo);
            }
            if (is_file(public_path('storage/'.$logo)) || is_file(storage_path('app/public/'.$logo))) {
                return asset('storage/'.$logo);
            }
        }

        // 5. Fallback kampus vs instansi
        if ($channel->scope_type === ChatConversation::SCOPE_UNIVERSITY) {
            return asset('images/default-university.svg');
        }

        return is_file(public_path('images/logos/surabaya.png'))
            ? asset('images/logos/surabaya.png')
            : (is_file(public_path('images/logoPemkotSBY.png'))
                ? asset('images/logoPemkotSBY.png')
                : asset('images/default-agency.svg'));
    }
}
