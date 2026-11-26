<?php

namespace App\Services\Institutions;

use App\Models\InstitutionChangeProposal;
use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Support\Facades\DB;

/**
 * Perbedaan dari API disimpan sebagai usul. Data lokal (termasuk koreksi manual admin) baru berubah
 * bila usul diterima; usul yang ditolak tidak diajukan lagi untuk nilai yang sama.
 */
class ChangeProposalService
{
    public function __construct(private MasterUniversityService $master) {}

    /** Bentuk pembanding: mengabaikan huruf besar-kecil, spasi, dan tanda baca. */
    public static function comparable(?string $value): string
    {
        return (string) preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower((string) $value));
    }

    /** Direktori menulis "Surabaya", data bawaan menulis "Kota Surabaya": sebutan wilayah diabaikan. */
    private static function comparableAddress(?string $address): string
    {
        return self::comparable((string) preg_replace('/\b(kota|kabupaten|kab|provinsi|prov)\b\.?/iu', ' ', (string) $address));
    }

    /**
     * Hanya untuk sekolah yang cocok lewat NPSN. $row adalah baris direktori yang sudah dinormalisasi.
     *
     * @param  array{name: string, address?: string, city?: string, province?: string}  $row
     * @return int jumlah usul baru
     */
    public function forSchool(University $university, array $row): int
    {
        $count = 0;
        $name = trim((string) $row['name']);
        // Nama panjang bawaan sering menyimpan nama resmi direktori di acronym: itu bukan perubahan.
        $knownNames = [self::comparable($university->name), self::comparable($university->acronym)];
        if ($name !== '' && ! in_array(self::comparable($name), $knownNames, true)) {
            $count += $this->propose($university->id, InstitutionChangeProposal::KIND_NAME, $university->name, $name);
        } elseif ($name !== '') {
            InstitutionChangeProposal::where(['university_id' => $university->id, 'kind' => InstitutionChangeProposal::KIND_NAME, 'status' => 'pending'])->delete();
        }

        $address = MasterUniversityService::directoryAddress($row);
        if ($address !== '' && trim((string) $university->address) !== ''
            && self::comparableAddress($address) !== self::comparableAddress($university->address)) {
            $count += $this->propose($university->id, InstitutionChangeProposal::KIND_ADDRESS, $university->address, $address);
        } elseif ($address !== '' && self::comparableAddress($address) === self::comparableAddress($university->address)) {
            InstitutionChangeProposal::where(['university_id' => $university->id, 'kind' => InstitutionChangeProposal::KIND_ADDRESS, 'status' => 'pending'])->delete();
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows  baris direktori kampus yang sudah dinormalisasi
     * @return int jumlah usul baru
     */
    public function forColleges(array $rows): int
    {
        $known = [];
        $codes = [];
        University::query()
            ->where(fn ($q) => $q->where('institution_type', University::TYPE_COLLEGE)->orWhereNull('institution_type'))
            ->get(['name', 'acronym', 'code'])
            ->each(function (University $u) use (&$known, &$codes) {
                $codes[$u->code] = true;
                if (preg_match('/^[0-9]{6}$/D', (string) $u->code)) {
                    return;
                }
                $known[self::comparable($u->name)] = true;
                $known[self::comparable($u->acronym)] = true;
            });

        $count = 0;
        $nameCounts = array_count_values(array_map(fn ($row) => self::comparable($row['name'] ?? ''), $rows));
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' || isset($codes[$row['official_code'] ?? '-']) || ($nameCounts[self::comparable($name)] === 1 && isset($known[self::comparable($name)]))) {
                continue;
            }
            $count += $this->propose(null, InstitutionChangeProposal::KIND_NEW_COLLEGE, null, $name, $row);
        }

        return $count;
    }

    /**
     * @param  array<int, int|string>  $ids
     * @param  'accept'|'reject'  $decision
     * @return int jumlah usul yang diputuskan (hanya yang masih menunggu)
     */
    public function decide(array $ids, string $decision, int $userId): int
    {
        return DB::transaction(function () use ($ids, $decision, $userId) {
            $proposals = InstitutionChangeProposal::with('university')
                ->whereIn('id', $ids)->where('status', 'pending')->lockForUpdate()->get();

            foreach ($proposals as $proposal) {
                if ($decision === 'accept') {
                    $this->apply($proposal);
                }
                $proposal->update([
                    'status' => $decision === 'accept' ? 'accepted' : 'rejected',
                    'decided_by' => $userId,
                    'decided_at' => now(),
                ]);
            }

            return $proposals->count();
        });
    }

    private function apply(InstitutionChangeProposal $proposal): void
    {
        if ($proposal->kind === InstitutionChangeProposal::KIND_NEW_COLLEGE) {
            $this->master->upsertDirectoryCollege($proposal->payload ?? ['name' => $proposal->proposed_value]);

            return;
        }

        $column = $proposal->kind === InstitutionChangeProposal::KIND_NAME ? 'name' : 'address';
        $proposal->university?->update([$column => $proposal->proposed_value]);
    }

    /** @param  array<string, mixed>|null  $payload */
    private function propose(?int $universityId, string $kind, ?string $current, string $proposed, ?array $payload = null): int
    {
        $proposed = mb_substr($proposed, 0, 500);
        $fingerprint = sha1($kind.'|'.($universityId ?? 0).'|'.(($payload['official_code'] ?? null) ?: self::comparable($proposed)));
        if (InstitutionChangeProposal::where('fingerprint', $fingerprint)->exists()) {
            return 0;
        }

        if ($universityId !== null) {
            InstitutionChangeProposal::where(['university_id' => $universityId, 'kind' => $kind, 'status' => 'pending'])->delete();
        }

        InstitutionChangeProposal::create([
            'university_id' => $universityId,
            'kind' => $kind,
            'current_value' => $current === null ? null : mb_substr($current, 0, 500),
            'proposed_value' => $proposed,
            'payload' => $payload,
            'fingerprint' => $fingerprint,
            'status' => 'pending',
        ]);

        return 1;
    }
}
