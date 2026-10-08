<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UniversityManagementService
{
    /**
     * Gabungkan kampus dobel (biasanya input mandiri mahasiswa) ke kampus yang benar.
     * Seluruh akun, profil mahasiswa, tiket, dan kanal chat dipindahkan ke kampus tujuan.
     */
    public function mergeUniversities(University $source, University $target): array
    {
        if ($target->id === $source->id) {
            throw new \InvalidArgumentException('Kampus tujuan tidak boleh sama dengan kampus yang digabungkan.');
        }

        return DB::transaction(function () use ($source, $target) {
            $users = User::where('university_id', $source->id)
                ->update(['university_id' => $target->id, 'university' => $target->name]);
            
            $profiles = StudentProfile::where('university_id', $source->id)
                ->update(['university_id' => $target->id, 'universitas' => $target->name]);
            
            SystemFeedback::where('target_university_id', $source->id)
                ->update(['target_university_id' => $target->id]);
            
            if (Schema::hasColumn('chat_conversations', 'university_id')) {
                DB::table('chat_conversations')->where('university_id', $source->id)->update(['university_id' => $target->id]);
            }
            
            $source->delete();

            AuditLog::record('UNIVERSITY_MERGE', 'University', $target->id, [
                'merged_from' => $source->name,
                'merged_from_id' => $source->id,
                'into' => $target->name,
                'moved_users' => $users,
                'moved_profiles' => $profiles,
            ]);

            return ['users' => $users, 'profiles' => $profiles];
        });
    }

    /**
     * Validasi kampus yang didaftarkan mandiri oleh mahasiswa (is_verified = false → true).
     */
    public function verifyUniversity(University $univ): bool
    {
        if ($univ->is_verified) {
            return false;
        }

        $univ->update(['is_verified' => true]);

        AuditLog::record('UNIVERSITY_VERIFY', 'University', $univ->id, [
            'name' => $univ->name,
        ]);

        return true;
    }
}
