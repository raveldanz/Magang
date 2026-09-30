<?php

return [

    /*
    | Batas ukuran lampiran per file (KB). Batas efektif = nilai terkecil antara angka ini
    | dan upload_max_filesize / post_max_size di php.ini (lihat ChatAttachmentStorage::maxUploadKb()).
    | Untuk menaikkan batas, ubah juga php.ini: upload_max_filesize & post_max_size.
    */
    'max_upload_kb' => (int) env('CHAT_MAX_UPLOAD_KB', 10240),

    // Jumlah lampiran maksimal dalam satu pesan
    'max_files_per_message' => 5,

    // Panjang maksimal teks satu pesan
    'max_body_length' => 5000,

    // Jumlah pesan per halaman saat memuat riwayat
    'page_size' => 30,

    // Anggota maksimal satu grup (termasuk pembuatnya)
    'group_max_members' => 100,

    // Durasi maksimal voice note (detik)
    'voice_note_max_seconds' => 300,

    // Ekstensi yang diizinkan, dicocokkan dengan jenis isi file (bukan sekadar nama file).
    // 'mpga' & 'oga' adalah ekstensi yang ditebak PHP untuk MP3 & OGG.
    'allowed_extensions' => [
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'video' => ['mp4', 'webm', 'mov'],
        'audio' => ['mp3', 'mpga', 'm4a', 'ogg', 'oga', 'opus', 'wav', 'aac', 'weba'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar'],
    ],

    // Alasan pelaporan pesan (kunci => label)
    'report_reasons' => [
        'pelecehan' => 'Pelecehan / perundungan',
        'tidak_pantas' => 'Konten tidak pantas',
        'spam' => 'Spam / promosi',
        'penipuan' => 'Penipuan / informasi palsu',
        'lainnya' => 'Lainnya',
    ],

    // Interval polling di browser (milidetik)
    'poll' => [
        'messages_visible' => 3000,
        'messages_hidden' => 20000,
        'summary_visible' => 15000,
        'summary_hidden' => 60000,
    ],

    // Dianggap online bila aktif dalam N detik terakhir
    'online_window_seconds' => 120,

    // Rate limit per pengguna per menit
    'rate_limits' => [
        'send' => 40,
        'poll' => 300,
    ],
];
