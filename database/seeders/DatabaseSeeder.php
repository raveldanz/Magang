<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\AgencyProfile;
use App\Models\StudentProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Evaluation;
use App\Models\FinalReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. SEED PROFIL MULTI-INSTANSI RESMI PEMERINTAH KOTA SURABAYA
        $agencies = [
            // Instansi 1: Diskominfo
            AgencyProfile::firstOrCreate(
                ['id' => 1],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Komunikasi Dan Informatika',
                    'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                    'phone' => '(031) 5312144',
                    'email' => 'diskominfo@surabaya.go.id',
                    'website' => 'https://diskominfo.surabaya.go.id',
                    'logo' => 'images/logos/diskominfo.png',
                    'signee_name' => 'Drs. H. M. NASER, M.Si',
                    'signee_nip' => '19700101 199503 1 002',
                    'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                    'city' => 'Surabaya',
                ]
            ),
            // Instansi 2: Dispusip
            AgencyProfile::firstOrCreate(
                ['id' => 2],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Perpustakaan Dan Kearsipan',
                    'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Surabaya 60293',
                    'phone' => '(031) 8704207',
                    'email' => 'dispusip@surabaya.go.id',
                    'website' => 'https://dispusip.surabaya.go.id',
                    'logo' => 'images/logos/dispusip.png',
                    'signee_name' => 'Ir. Mia Santi Dewi, M.Si',
                    'signee_nip' => '19680812 199403 2 007',
                    'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                    'city' => 'Surabaya',
                ]
            ),
            // Instansi 3: Dispendukcapil
            AgencyProfile::firstOrCreate(
                ['id' => 3],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Kependudukan Dan Pencatatan Sipil',
                    'address' => 'Jl. Manyar Kertoarjo No. 1, Manyar Sabrangan, Kec. Mulyorejo, Surabaya 60116',
                    'phone' => '(031) 5913222',
                    'email' => 'dispendukcapil@surabaya.go.id',
                    'website' => 'https://dispendukcapil.surabaya.go.id',
                    'logo' => 'images/logos/dispendukcapil.png',
                    'signee_name' => 'Eddy Christijanto, Drs., M.Si',
                    'signee_nip' => '19670615 199303 1 005',
                    'signee_position' => 'Kepala Dinas Kependudukan dan Pencatatan Sipil',
                    'city' => 'Surabaya',
                ]
            ),
        ];

        // 1. Seed User Admin (Utama & Masing-Masing Instansi Pemkot Surabaya)
        $adminUtama = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'agency_profile_id' => $agencies[0]->id,
            ]
        );

        $adminDiskominfo = User::firstOrCreate(
            ['email' => 'admin.diskominfo@surabaya.go.id'],
            [
                'name' => 'Admin Diskominfo Surabaya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'agency_profile_id' => $agencies[0]->id,
            ]
        );

        $adminDispusip = User::firstOrCreate(
            ['email' => 'admin.dispusip@surabaya.go.id'],
            [
                'name' => 'Admin Dispusip Surabaya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'agency_profile_id' => $agencies[1]->id,
            ]
        );

        $adminDispendukcapil = User::firstOrCreate(
            ['email' => 'admin.dispendukcapil@surabaya.go.id'],
            [
                'name' => 'Admin Dispendukcapil Surabaya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'agency_profile_id' => $agencies[2]->id,
            ]
        );


        // 2. SEED 6 AKUN PEMBIMBING LAPANGAN (ASN & PRAKTISI RESMI PEMKOT SURABAYA)
        $pembimbingData = [
            [
                'name' => 'Retno Mumpuni, S.Kom., M.Sc',
                'email' => 'retnomumpuni.if@upnjatim.ac.id',
            ],
            [
                'name' => 'Budi Santoso, S.ST., M.MT',
                'email' => 'budi.santoso@surabaya.go.id',
            ],
            [
                'name' => 'Ir. Siti Aminah, M.Kom',
                'email' => 'siti.aminah@surabaya.go.id',
            ],
            [
                'name' => 'Hendra Wijaya, S.Kom., M.Eng',
                'email' => 'hendra.wijaya@surabaya.go.id',
            ],
            [
                'name' => 'Tri Wahyuni, S.T., M.Sc',
                'email' => 'tri.wahyuni@surabaya.go.id',
            ],
            [
                'name' => 'M. Arif Rahman, S.Kom., M.MT',
                'email' => 'arif.rahman@surabaya.go.id',
            ],
        ];

        $pembimbings = [];
        foreach ($pembimbingData as $pData) {
            $pembimbings[] = User::firstOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'password' => Hash::make('pembimbing123'),
                    'role' => 'pembimbing',
                ]
            );
        }

        // 3. SEED UNIT KERJA TERHUBUNG DENGAN AGENCY PROFILE MASING-MASING
        $unitData = [
            // Unit Diskominfo (Agency ID 1)
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Layanan Informatika & E-Government',
                'description' => 'Pengembangan arsitektur SPBE, integrasi aplikasi layanan publik Pemkot Surabaya, dan portal WargaKu',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Pengelolaan Informasi & Komunikasi Publik',
                'description' => 'Pengelolaan media komunikasi resmi, saluran pengaduan masyarakat, kehumasan, dan keterbukaan informasi publik (PPID)',
                'quota' => 8,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Infrastruktur TI & Jaringan Komunikasi',
                'description' => 'Pemeliharaan jaringan fiber optic intra-pemerintah, Data Center Tier-3 Pemkot, cloud infrastructure, dan free wifi publik',
                'quota' => 8,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)',
                'description' => 'Pusat tanggap insiden siber (CSIRT), implementasi TTE BSrE, enkripsi data, dan audit keamanan sistem informasi',
                'quota' => 6,
            ],
            // Unit Dispusip (Agency ID 2)
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital (Dispusip)',
                'description' => 'Digitalisasi koleksi naskah kuno, sistem temu kembali arsip digital, dan otomasi perpustakaan daerah',
                'quota' => 6,
            ],
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik',
                'description' => 'Pengelolaan arsip digital dinas, alih media dokumen bersejarah Kota Surabaya, dan repositori arsip elektronik',
                'quota' => 5,
            ],
            // Unit Dispendukcapil (Agency ID 3)
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pengelolaan Informasi Administrasi Kependudukan (PIAK)',
                'description' => 'Integrasi sistem database kependudukan Klampid New Generation (KNG) dan keamanan data kependudukan',
                'quota' => 6,
            ],
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pemanfaatan Data & Inovasi Pelayanan Kependudukan',
                'description' => 'Inovasi integrasi data kependudukan dengan BPJS, Dinsos, dan perbankan daerah untuk percepatan layanan publik',
                'quota' => 5,
            ],
        ];

        $units = [];
        foreach ($unitData as $uData) {
            $units[] = Unit::firstOrCreate(
                ['name' => $uData['name']],
                [
                    'agency_profile_id' => $uData['agency_profile_id'],
                    'description' => $uData['description'],
                    'quota' => $uData['quota'],
                ]
            );
        }

        // 4. SEED DATA MAHASISWA TESTING TAMBAHAN (PENDING, VERIFIED, REJECTED)
        // Mahasiswa 1: Raveldo Andyka (PENDING)
        $mhs1 = User::firstOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            ['name' => 'Raveldo Andyka', 'password' => Hash::make('mahasiswa123'), 'role' => 'mahasiswa']
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs1->id],
            ['nim' => '22081010001', 'universitas' => 'UPN Veteran Jawa Timur', 'jurusan' => 'Informatika', 'phone' => '081234567890']
        );
        Application::firstOrCreate(
            ['user_id' => $mhs1->id],
            [
                'unit_id' => $units[0]->id,
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'pending',
            ]
        );

        // Mahasiswa 2: Dimas Adam (VERIFIED)
        $mhs2 = User::firstOrCreate(
            ['email' => 'dimas.adam@mhs.unair.ac.id'],
            ['name' => 'Dimas Adam', 'password' => Hash::make('mahasiswa123'), 'role' => 'mahasiswa']
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs2->id],
            ['nim' => '22081010002', 'universitas' => 'Universitas Airlangga', 'jurusan' => 'Sistem Informasi', 'phone' => '089876543210']
        );
        Application::firstOrCreate(
            ['user_id' => $mhs2->id],
            [
                'unit_id' => $units[1]->id,
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'verified',
            ]
        );

        // Mahasiswa 3: Siti Nurhaliza (REJECTED)
        $mhs3 = User::firstOrCreate(
            ['email' => 'siti.nurhaliza@mhs.unesa.ac.id'],
            ['name' => 'Siti Nurhaliza', 'password' => Hash::make('mahasiswa123'), 'role' => 'mahasiswa']
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs3->id],
            ['nim' => '22081010003', 'universitas' => 'Universitas Negeri Surabaya', 'jurusan' => 'Teknik Informatika', 'phone' => '087654321098']
        );
        Application::firstOrCreate(
            ['user_id' => $mhs3->id],
            [
                'unit_id' => $units[3]->id,
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'rejected',
                'rejection_note' => 'Dokumen Portofolio dan Transkrip Nilai belum terlampir dengan jelas. Silakan ajukan ulang berkas yang lengkap.',
            ]
        );

        // 5. SEED 10 MAHASISWA LULUS DENGAN DISTRIBUSI UNIT DAN PEMBIMBING BERAGAM MULTI-INSTANSI
        $graduatedStudents = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@mhs.unair.ac.id',
                'nim' => '22081010011',
                'universitas' => 'Universitas Airlangga',
                'jurusan' => 'Sistem Informasi',
                'phone' => '081234560011',
            ],
            [
                'name' => 'Nabila Putri Pratama',
                'email' => 'nabila.putri@mhs.upnjatim.ac.id',
                'nim' => '22081010012',
                'universitas' => 'UPN Veteran Jawa Timur',
                'jurusan' => 'Informatika',
                'phone' => '081234560012',
            ],
            [
                'name' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@mhs.its.ac.id',
                'nim' => '22081010013',
                'universitas' => 'Institut Teknologi Sepuluh Nopember',
                'jurusan' => 'Teknik Informatika',
                'phone' => '081234560013',
            ],
            [
                'name' => 'Anisa Rahmawati',
                'email' => 'anisa.rahma@mhs.ub.ac.id',
                'nim' => '22081010014',
                'universitas' => 'Universitas Brawijaya',
                'jurusan' => 'Teknologi Informasi',
                'phone' => '081234560014',
            ],
            [
                'name' => 'Fajar Dwi Santoso',
                'email' => 'fajar.dwi@mhs.unesa.ac.id',
                'nim' => '22081010015',
                'universitas' => 'Universitas Negeri Surabaya',
                'jurusan' => 'Teknik Informatika',
                'phone' => '081234560015',
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@mhs.dinamika.ac.id',
                'nim' => '22081010016',
                'universitas' => 'Universitas Dinamika',
                'jurusan' => 'Sistem Informasi',
                'phone' => '081234560016',
            ],
            [
                'name' => 'Bagus Tri Wicaksono',
                'email' => 'bagus.tri@mhs.uinsa.ac.id',
                'nim' => '22081010017',
                'universitas' => 'UIN Sunan Ampel Surabaya',
                'jurusan' => 'Sains Komputer',
                'phone' => '081234560017',
            ],
            [
                'name' => 'Clara Salsabila',
                'email' => 'clara.salsabila@mhs.uc.ac.id',
                'nim' => '22081010018',
                'universitas' => 'Universitas Ciputra',
                'jurusan' => 'Informatika',
                'phone' => '081234560018',
            ],
            [
                'name' => 'Hafidz Maulana',
                'email' => 'hafidz.m@mhs.pens.ac.id',
                'nim' => '22081010019',
                'universitas' => 'Politeknik Elektronika Negeri Surabaya',
                'jurusan' => 'Teknik Informatika Terapan',
                'phone' => '081234560019',
            ],
            [
                'name' => 'Putri Maharani',
                'email' => 'putri.maharani@mhs.ubaya.ac.id',
                'nim' => '22081010020',
                'universitas' => 'Universitas Surabaya',
                'jurusan' => 'Sistem Informasi Bisnis',
                'phone' => '081234560020',
            ],
        ];

        $startDate = Carbon::now()->subMonths(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $evaluationNotes = [
            'Sangat memuaskan, proaktif, dan menyelesaikan tugas-tugas magang dengan hasil optimal.',
            'Disiplin tinggi, komunikasi tim sangat baik, dan menguasai tools teknis unit kerja dengan cepat.',
            'Inisiatif luar biasa dalam pemecahan masalah serta dokumentasi laporan yang terstruktur rapi.',
            'Kinerja sangat memuaskan, selalu hadir tepat waktu, dan berkontribusi aktif pada project tim.',
            'Mampu bekerja mandiri maupun kolaboratif, hasil kerja berkualitas tinggi dan sesuai target.',
        ];

        foreach ($graduatedStudents as $index => $data) {
            // A. Buat User Mahasiswa
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('mahasiswa123'),
                    'role' => 'mahasiswa',
                ]
            );

            // B. Buat Profil Mahasiswa
            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nim' => $data['nim'],
                    'universitas' => $data['universitas'],
                    'jurusan' => $data['jurusan'],
                    'phone' => $data['phone'],
                ]
            );

            // C. Distribusi Acak / Bergantian Unit Kerja & Pembimbing Lapangan
            $assignedUnit = $units[$index % count($units)];
            $assignedPembimbing = $pembimbings[$index % count($pembimbings)];

            // D. Buat Application Status ACCEPTED dengan rentang waktu 3 bulan lalu s.d. hari ini
            $application = Application::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'unit_id' => $assignedUnit->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'accepted',
                    'letter_number' => '500.12.2/' . str_pad($index + 10, 3, '0', STR_PAD_LEFT) . '/436.7.14/' . date('Y'),
                    'letter_date' => Carbon::now()->subMonths(3)->subDays(5)->format('Y-m-d'),
                ]
            );

            // E. Buat Placement dengan Pembimbing Terdistribusi
            $placement = Placement::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'pembimbing_id' => $assignedPembimbing->id,
                ]
            );

            // F. Buat Laporan Akhir (Approved)
            FinalReport::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'file_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                    'status' => 'approved',
                    'feedback' => 'Laporan akhir magang telah diperiksa, disetujui, dan memenuhi standar instansi.',
                ]
            );

            // G. Buat Penilaian Evaluasi (Nilai 80 - 98)
            $disiplin = rand(82, 96);
            $kinerja = rand(85, 98);
            $laporan = rand(80, 95);
            $catatan = $evaluationNotes[$index % count($evaluationNotes)];

            Evaluation::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'nilai_disiplin' => $disiplin,
                    'nilai_kinerja' => $kinerja,
                    'nilai_laporan' => $laporan,
                    'catatan' => $catatan,
                ]
            );
        }
    }
}