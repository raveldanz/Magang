<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * Nilai Akhir Pembimbing Lapangan Dinas (Rata-rata 3 Aspek: Disiplin, Kinerja/Teknis, Laporan/Inisiatif)
     */
    public function getNilaiPembimbingAttribute()
    {
        $sum = ($this->nilai_disiplin ?? 0) + ($this->nilai_kinerja ?? 0) + ($this->nilai_laporan ?? 0);
        return $sum > 0 ? round($sum / 3, 2) : 0;
    }

    /**
     * Nilai Akademik Dosen Pembimbing Lapangan (Rata-rata 3 Aspek: Mastery, Report, Attitude)
     */
    public function getNilaiDosenCalculatedAttribute()
    {
        if (isset($this->attributes['nilai_dosen']) && (float)$this->attributes['nilai_dosen'] > 0) {
            return (float)$this->attributes['nilai_dosen'];
        }

        $sumSub = ($this->score_mastery ?? 0) + ($this->score_report ?? 0) + ($this->score_attitude ?? 0);
        if ($sumSub > 0) {
            return round($sumSub / 3, 2);
        }

        return (float)($this->nilai_akademik ?? 0);
    }

    /**
     * Dapatkan data Universitas asal Mahasiswa
     */
    public function getUniversity()
    {
        $student = $this->placement?->application?->user;
        if (!$student) return null;

        if ($student->university_id) {
            return University::find($student->university_id);
        }

        $name = $student->university ?? $student->studentProfile?->universitas;
        if ($name) {
            return University::where('name', 'like', "%{$name}%")->orWhere('code', 'like', "%{$name}%")->first();
        }

        return null;
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
        $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
        $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;

        if ($scheme === 'mentor_only') {
            if ($nilaiDinas > 0) {
                return (float)$nilaiDinas;
            }
            return (float)($this->attributes['final_score'] ?? 0);
        }

        if ($nilaiDinas > 0 && $nilaiDosen > 0) {
            return (float)round(($nilaiDinas * ($weightMentor / 100)) + ($nilaiDosen * ($weightLecturer / 100)), 2);
        }

        if (isset($this->attributes['final_score']) && (float)$this->attributes['final_score'] > 0) {
            return (float)$this->attributes['final_score'];
        }

        if ($nilaiDinas > 0) {
            return (float)$nilaiDinas;
        } elseif ($nilaiDosen > 0) {
            return (float)$nilaiDosen;
        }

        return 0;
    }

    /**
     * Huruf Mutu (Grade)
     */
    public function getGradeCalculatedAttribute()
    {
        if (!empty($this->attributes['grade'])) {
            return $this->attributes['grade'];
        }

        $score = $this->nilai_akhir;
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'AB';
        if ($score >= 65) return 'B';
        if ($score >= 55) return 'BC';
        if ($score >= 40) return 'C';
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
