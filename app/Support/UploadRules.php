<?php

namespace App\Support;

/**
 * Satu sumber batas ukuran & format unggahan berkas (dipakai validasi controller dan teks bantuan view).
 * Nilai max dalam kilobyte sesuai aturan validasi Laravel.
 */
final class UploadRules
{
    /** Dokumen persyaratan pengajuan (surat pengantar, CV, transkrip): PDF maks 2MB */
    public const APPLICATION_DOCUMENT = 'file|mimes:pdf|max:2048';

    /** KTM / kartu identitas: hasil pindai boleh PDF atau gambar, tetap maks 2MB */
    public const APPLICATION_ID_CARD = 'file|mimes:pdf,jpg,jpeg,png|max:2048';

    /** Lampiran logbook harian mahasiswa: maks 2MB */
    public const LOGBOOK_ATTACHMENT = 'file|mimes:jpg,jpeg,png,pdf|max:2048';

    /** Naskah laporan akhir magang: PDF maks 5MB */
    public const FINAL_REPORT = 'file|mimes:pdf|max:5120';

    public const MAX_DOCUMENT_KB = 2048;

    public const MAX_FINAL_REPORT_KB = 5120;
}
