<?php

namespace App\Models;

use App\Services\UniversityResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'custom_components' => 'array',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * Mengambil daftar komponen penilaian Mentor Lapangan Dinas (dinamis atau fallback ke 3 kriteria standar).
     * @return array<int, array{name: string, score: float|int|null}>
     */
    public function getMentorComponents(): array
    {
        $custom = $this->custom_components;
        if (is_array($custom)) {
            if (isset($custom['mentor']) && is_array($custom['mentor']) && ! empty($custom['mentor'])) {
                return $custom['mentor'];
            }
            if (array_is_list($custom) && ! empty($custom) && ! isset($custom['lecturer'])) {
                return $custom;
            }
        }

        $univ = $this->getUniversity();

        return [
            [
                'name' => $univ ? $univ->getDinasComponent(1) : \App\Models\University::DEFAULT_DINAS_COMPONENTS[1],
                'score' => $this->nilai_disiplin !== null ? (float) $this->nilai_disiplin : null,
            ],
            [
                'name' => $univ ? $univ->getDinasComponent(2) : \App\Models\University::DEFAULT_DINAS_COMPONENTS[2],
                'score' => $this->nilai_kinerja !== null ? (float) $this->nilai_kinerja : null,
            ],
            [
                'name' => $univ ? $univ->getDinasComponent(3) : \App\Models\University::DEFAULT_DINAS_COMPONENTS[3],
                'score' => $this->nilai_laporan !== null ? (float) $this->nilai_laporan : null,
            ],
        ];
    }

    /**
     * Mengambil daftar komponen penilaian Dosen Pembimbing Lapangan (dinamis atau fallback ke 3 kriteria standar).
     * @return array<int, array{name: string, score: float|int|null}>
     */
    public function getLecturerComponents(): array
    {
        $custom = $this->custom_components;
        if (is_array($custom) && isset($custom['lecturer']) && is_array($custom['lecturer']) && ! empty($custom['lecturer'])) {
            return $custom['lecturer'];
        }

        $univ = $this->getUniversity();

        return [
            [
                'name' => $univ ? $univ->getDplComponent(1) : \App\Models\University::DEFAULT_DPL_COMPONENTS[1],
                'score' => $this->dosenAspectScore('score_mastery'),
            ],
            [
                'name' => $univ ? $univ->getDplComponent(2) : \App\Models\University::DEFAULT_DPL_COMPONENTS[2],
                'score' => $this->dosenAspectScore('score_report'),
            ],
            [
                'name' => $univ ? $univ->getDplComponent(3) : \App\Models\University::DEFAULT_DPL_COMPONENTS[3],
                'score' => $this->dosenAspectScore('score_attitude'),
            ],
        ];
    }

    /**
     * Nilai Akhir Pembimbing Lapangan Dinas (Rata-rata aspek dinas dari custom_components atau 3 aspek standar)
     */
    public function getNilaiPembimbingAttribute()
    {
        $custom = $this->custom_components;
        $items = null;
        if (is_array($custom)) {
            if (isset($custom['mentor']) && is_array($custom['mentor']) && ! empty($custom['mentor'])) {
                $items = $custom['mentor'];
            } elseif (array_is_list($custom) && ! empty($custom) && ! isset($custom['lecturer'])) {
                $items = $custom;
            }
        }

        if ($items && count($items) > 0) {
            $scores = array_filter(array_map(fn ($it) => isset($it['score']) && is_numeric($it['score']) ? (float) $it['score'] : null, $items), fn ($s) => $s !== null);
            if (count($scores) > 0) {
                return round(array_sum($scores) / count($scores), 2);
            }
        }

        $sum = ($this->nilai_disiplin ?? 0) + ($this->nilai_kinerja ?? 0) + ($this->nilai_laporan ?? 0);

        return $sum > 0 ? round($sum / 3, 2) : 0;
    }

    /**
     * Nilai Akademik Dosen Pembimbing Lapangan (Rata-rata aspek dosen dari custom_components atau 3 aspek standar)
     */
    public function getNilaiDosenCalculatedAttribute()
    {
        $custom = $this->custom_components;
        if (is_array($custom) && isset($custom['lecturer']) && is_array($custom['lecturer']) && ! empty($custom['lecturer'])) {
            $scores = array_filter(array_map(fn ($it) => isset($it['score']) && is_numeric($it['score']) ? (float) $it['score'] : null, $custom['lecturer']), fn ($s) => $s !== null);
            if (count($scores) > 0) {
                return round(array_sum($scores) / count($scores), 2);
            }
        }

        if (isset($this->attributes['dosen_evaluation_score']) && (float) $this->attributes['dosen_evaluation_score'] > 0) {
            return (float) $this->attributes['dosen_evaluation_score'];
        }

        if (isset($this->attributes['nilai_dosen']) && (float) $this->attributes['nilai_dosen'] > 0) {
            return (float) $this->attributes['nilai_dosen'];
        }

        $sumSub = ($this->score_mastery ?? 0) + ($this->score_report ?? 0) + ($this->score_attitude ?? 0);
        if ($sumSub > 0) {
            return round($sumSub / 3, 2);
        }

        return (float) ($this->nilai_akademik ?? 0);
    }

    /**
     * Nilai per aspek DPL (score_mastery / score_report / score_attitude) untuk ditampilkan.
     * Kolom aspek default-nya 0 (bukan null), jadi operator ?? tidak pernah jatuh ke fallback.
     * Jika aspek belum diisi (mis. data lama/dummy hanya punya nilai_akademik), tampilkan nilai DPL rata-rata.
     */
    public function dosenAspectScore(string $field): ?float
    {
        $value = (float) ($this->attributes[$field] ?? 0);
        if ($value > 0) {
            return $value;
        }

        $fallback = (float) $this->nilai_dosen_calculated;

        return $fallback > 0 ? $fallback : null;
    }

    /**
     * Cache hasil getUniversity() per instance: accessor nilai_akhir, is_complete, dan
     * grade_calculated masing-masing memanggilnya, sehingga tanpa cache satu baris tabel
     * bisa memicu 3+ query yang sama. false = belum pernah di-resolve.
     */
    protected $resolvedUniversity = false;

    /**
     * Dapatkan data Universitas asal Mahasiswa
     */
    public function getUniversity()
    {
        if ($this->resolvedUniversity !== false) {
            return $this->resolvedUniversity;
        }

        return $this->resolvedUniversity = $this->findUniversity();
    }

    /**
     * Tetapkan universitas yang sudah diketahui pemanggil (mis. halaman detail kampus),
     * agar accessor nilai tidak perlu menelusuri placement → application → user lagi.
     */
    public function useUniversity(?University $university): static
    {
        $this->resolvedUniversity = $university;

        return $this;
    }

    public function refresh()
    {
        $this->resolvedUniversity = false;

        return parent::refresh();
    }

    private function findUniversity(): ?University
    {
        $student = $this->placement?->application?->user;
        if (! $student) {
            return null;
        }

        // university_id → profil → nama persis (bukan LIKE yang bisa salah memilih kampus)
        return app(UniversityResolver::class)->forUser($student);
    }

    /**
     * Cek apakah lembar evaluasi sudah lengkap sesuai skema kebijakan universitas
     */
    public function getIsCompleteAttribute(): bool
    {
        $univ = $this->getUniversity();
        $scheme = $univ->evaluation_scheme ?? 'mentor_only';
        $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
        $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

        $hasMentor = $this->nilai_pembimbing > 0;
        $hasDosen = $this->nilai_dosen_calculated > 0 || ($this->nilai_dosen ?? 0) > 0 || ($this->nilai_akademik ?? 0) > 0;

        if ((float) ($this->attributes['final_score'] ?? 0) > 0) {
            return true;
        }

        // 1. Skema 100% Dinas
        if ($scheme === 'mentor_only' || $weightLecturer === 0) {
            return $hasMentor;
        }

        // 2. Skema 100% Universitas / DPL
        if ($weightMentor === 0) {
            return $hasDosen;
        }

        // 3. Skema Kolaborasi Dua Pihak
        return $hasMentor && $hasDosen;
    }

    /**
     * Nilai Akhir Gabungan Adaptif Berdasarkan Kebijakan Kampus
     */
    public function getNilaiAkhirAttribute()
    {
        $nilaiDinas = $this->nilai_pembimbing;
        $nilaiDosen = $this->nilai_dosen_calculated;
        $univ = $this->getUniversity();

        $scheme = $univ->evaluation_scheme ?? 'mentor_only';
        $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
        $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

        // 1. Jika terdapat final_score resmi yang tersimpan di DB
        if (isset($this->attributes['final_score']) && (float) $this->attributes['final_score'] > 0) {
            return (float) $this->attributes['final_score'];
        }

        // 2. Skema Penilaian Penuh Dinas (100% Mentor)
        if ($scheme === 'mentor_only' || $weightLecturer === 0) {
            return (float) $nilaiDinas;
        }

        // 3. Skema Penilaian Penuh Kampus (100% DPL)
        if ($weightMentor === 0) {
            return (float) $nilaiDosen;
        }

        // 4. Skema Penilaian Ganda (Dual Evaluation: Mentor + DPL)
        // Wajib lengkap keduanya agar menghasilkan nilai akhir sah
        if ($nilaiDinas > 0 && $nilaiDosen > 0) {
            return (float) round(($nilaiDinas * ($weightMentor / 100)) + ($nilaiDosen * ($weightLecturer / 100)), 2);
        }

        return 0.0;
    }

    /**
     * Huruf Mutu (Grade)
     */
    public function getGradeCalculatedAttribute()
    {
        if (! empty($this->attributes['grade'])) {
            return $this->attributes['grade'];
        }

        if (! $this->is_complete) {
            return '-';
        }

        $score = $this->nilai_akhir;
        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 75) {
            return 'AB';
        }
        if ($score >= 65) {
            return 'B';
        }
        if ($score >= 55) {
            return 'BC';
        }
        if ($score >= 40) {
            return 'C';
        }

        return $score > 0 ? 'E' : '-';
    }

    /**
     * Predikat Kelulusan
     */
    public function getPredikatAttribute()
    {
        $grade = $this->grade_calculated;

        return match ($grade) {
            'A' => 'Dengan Pujian (Sangat Memuaskan)',
            'AB', 'B' => 'Sangat Baik',
            'BC', 'C' => 'Baik',
            'E' => 'Kurang / Mengulang',
            default => 'Belum Lulus',
        };
    }
}
