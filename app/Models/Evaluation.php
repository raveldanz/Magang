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
        'dosen_aspects' => 'array',
        'mentor_aspects' => 'array',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * Nilai Akhir Pembimbing Lapangan Dinas (Rata-rata aspek kriteria kedinasan dinamis / legacy 3 aspek)
     */
    public function getNilaiPembimbingAttribute()
    {
        if (! empty($this->mentor_aspects) && is_array($this->mentor_aspects)) {
            $scores = [];
            foreach ($this->mentor_aspects as $item) {
                if (isset($item['score']) && is_numeric($item['score'])) {
                    $scores[] = (float) $item['score'];
                }
            }
            if (count($scores) > 0) {
                return round(array_sum($scores) / count($scores), 2);
            }
        }

        $sum = ($this->nilai_disiplin ?? 0) + ($this->nilai_kinerja ?? 0) + ($this->nilai_laporan ?? 0);

        return $sum > 0 ? round($sum / 3, 2) : 0;
    }

    /**
     * Nilai Akademik Dosen Pembimbing Lapangan (Rata-rata aspek kriteria kampus dinamis / legacy 3 aspek)
     */
    public function getNilaiDosenCalculatedAttribute()
    {
        if (! empty($this->dosen_aspects) && is_array($this->dosen_aspects)) {
            $scores = [];
            foreach ($this->dosen_aspects as $item) {
                if (isset($item['score']) && is_numeric($item['score'])) {
                    $scores[] = (float) $item['score'];
                }
            }
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
     * Dapatkan daftar aspek penilaian DPL yang terformat rapi (nama + skor)
     */
    public function getDosenAspectsListAttribute(): array
    {
        if (! empty($this->dosen_aspects) && is_array($this->dosen_aspects)) {
            return $this->dosen_aspects;
        }

        $list = [];
        if (($this->score_mastery ?? 0) > 0 || ($this->score_report ?? 0) > 0 || ($this->score_attitude ?? 0) > 0) {
            $list[] = ['name' => 'Penguasaan Materi & Relevansi CPL', 'score' => (float) $this->score_mastery];
            $list[] = ['name' => 'Kualitas Laporan Akhir Magang', 'score' => (float) $this->score_report];
            $list[] = ['name' => 'Sikap, Komunikasi & Etika Bimbingan', 'score' => (float) $this->score_attitude];
        } elseif (($this->nilai_dosen ?? 0) > 0 || ($this->nilai_akademik ?? 0) > 0) {
            $score = (float) ($this->nilai_dosen > 0 ? $this->nilai_dosen : $this->nilai_akademik);
            $list[] = ['name' => 'Penilaian Akademik DPL', 'score' => $score];
        }

        return $list;
    }

    /**
     * Dapatkan daftar aspek penilaian Mentor yang terformat rapi (nama + skor)
     */
    public function getMentorAspectsListAttribute(): array
    {
        if (! empty($this->mentor_aspects) && is_array($this->mentor_aspects)) {
            return $this->mentor_aspects;
        }

        $list = [];
        if (($this->nilai_disiplin ?? 0) > 0 || ($this->nilai_kinerja ?? 0) > 0 || ($this->nilai_laporan ?? 0) > 0) {
            $list[] = ['name' => 'Kedisiplinan & Tata Tertib Kerja', 'score' => (float) $this->nilai_disiplin];
            $list[] = ['name' => 'Kinerja Teknis & Kerjasama Tim', 'score' => (float) $this->nilai_kinerja];
            $list[] = ['name' => 'Inisiatif & Laporan Kedinasan', 'score' => (float) $this->nilai_laporan];
        }

        return $list;
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
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $hasMentor = $this->nilai_pembimbing > 0;
        $hasDosen = $this->nilai_dosen_calculated > 0 || ($this->nilai_dosen ?? 0) > 0 || ($this->nilai_akademik ?? 0) > 0;

        if ((float) ($this->attributes['final_score'] ?? 0) > 0) {
            return true;
        }

        if ($scheme === 'mentor_only') {
            return $hasMentor;
        }

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

        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $weightMentor = $univ ? (int) $univ->weight_mentor : 40;
        $weightLecturer = $univ ? (int) $univ->weight_lecturer : 60;

        // 1. Jika terdapat final_score resmi yang tersimpan di DB
        if (isset($this->attributes['final_score']) && (float) $this->attributes['final_score'] > 0) {
            return (float) $this->attributes['final_score'];
        }

        // 2. Skema Penilaian Penuh Dinas (100% Mentor)
        if ($scheme === 'mentor_only') {
            return (float) $nilaiDinas;
        }

        // 3. Skema Penilaian Ganda (Dual Evaluation: Mentor + DPL)
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
