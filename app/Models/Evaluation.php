<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'placement_id',
        'nilai_disiplin',
        'nilai_kinerja',
        'nilai_laporan',
        'nilai_akademik',
        'catatan',
        'catatan_dosen',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * Nilai Akhir Pembimbing Dinas (Rata-rata 3 Aspek: Disiplin, Kinerja/Teknis, Laporan/Inisiatif)
     */
    public function getNilaiPembimbingAttribute()
    {
        $sum = ($this->nilai_disiplin ?? 0) + ($this->nilai_kinerja ?? 0) + ($this->nilai_laporan ?? 0);
        return $sum > 0 ? round($sum / 3, 2) : 0;
    }

    /**
     * Nilai Akhir Gabungan (40% Instansi Dinas + 60% Dosen Kampus)
     */
    public function getNilaiAkhirAttribute()
    {
        $nilaiDinas = $this->nilai_pembimbing;
        $nilaiDosen = $this->nilai_akademik ?? 0;

        if ($nilaiDinas > 0 && $nilaiDosen > 0) {
            return round(($nilaiDinas * 0.4) + ($nilaiDosen * 0.6), 2);
        } elseif ($nilaiDinas > 0) {
            return $nilaiDinas;
        } elseif ($nilaiDosen > 0) {
            return $nilaiDosen;
        }

        return 0;
    }
}
