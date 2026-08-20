<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\AgencyProfile;
use App\Models\University;
use App\Models\StudentProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. SEED MASTER DATA UNIVERSITAS DI SURABAYA
        $universities = [
            'UNITOMO' => University::firstOrCreate(
                ['code' => 'UNITOMO'],
                ['name' => 'Universitas Dr. Soetomo']
            ),
            'UNESA' => University::firstOrCreate(
                ['code' => 'UNESA'],
                ['name' => 'Universitas Negeri Surabaya']
            ),
            'ITS' => University::firstOrCreate(
                ['code' => 'ITS'],
                ['name' => 'Institut Teknologi Sepuluh Nopember']
            ),
            'UNAIR' => University::firstOrCreate(
                ['code' => 'UNAIR'],
                ['name' => 'Universitas Airlangga']
            ),
            'UPN' => University::firstOrCreate(
                ['code' => 'UPN'],
                ['name' => 'UPN Veteran Jawa Timur']
            ),
        ];

        // 1. SEED PROFIL MULTI-INSTANSI RESMI PEMERINTAH KOTA SURABAYA
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

        // 2. SEED USER ADMIN (Utama & Masing-Masing Instansi Pemkot Surabaya)
        $adminUtama = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'agency_profile_id' => null,
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

        // 3. SEED 3 AKUN MENTOR PER INSTANSI PEMKOT SURABAYA (TOTAL 9 MENTOR)
        // A. Diskominfo (Agency ID 1)
        $mentorsDiskominfo = [
            User::firstOrCreate(
                ['email' => 'mentor.kominfo1@surabaya.go.id'],
                [
                    'name' => 'Retno Mumpuni, S.Kom., M.Sc',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[0]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.kominfo2@surabaya.go.id'],
                [
                    'name' => 'Ir. Siti Aminah, M.Kom',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[0]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.kominfo3@surabaya.go.id'],
                [
                    'name' => 'M. Arif Rahman, S.Kom., M.MT',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[0]->id,
                ]
            ),
        ];

        // B. Dispusip (Agency ID 2)
        $mentorsDispusip = [
            User::firstOrCreate(
                ['email' => 'mentor.dispusip1@surabaya.go.id'],
                [
                    'name' => 'Budi Santoso, S.ST., M.MT',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[1]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.dispusip2@surabaya.go.id'],
                [
                    'name' => 'Dewi Lestari, S.Sos., M.A',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[1]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.dispusip3@surabaya.go.id'],
                [
                    'name' => 'Agus Pramono, S.Hum., M.P',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[1]->id,
                ]
            ),
        ];

        // C. Dispendukcapil (Agency ID 3)
        $mentorsDispendukcapil = [
            User::firstOrCreate(
                ['email' => 'mentor.dukcapil1@surabaya.go.id'],
                [
                    'name' => 'Hendra Wijaya, S.Kom., M.Eng',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[2]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.dukcapil2@surabaya.go.id'],
                [
                    'name' => 'Tri Wahyuni, S.T., M.Sc',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[2]->id,
                ]
            ),
            User::firstOrCreate(
                ['email' => 'mentor.dukcapil3@surabaya.go.id'],
                [
                    'name' => 'Bambang Sutrisno, S.AP., M.AP',
                    'password' => Hash::make('password'),
                    'role' => 'mentor',
                    'agency_profile_id' => $agencies[2]->id,
                ]
            ),
        ];

        $allMentors = array_merge($mentorsDiskominfo, $mentorsDispusip, $mentorsDispendukcapil);

        // 4. SEED 2 AKUN DOSEN PEMBIMBING LAPANGAN (DPL) PER KAMPUS (TOTAL 10 DOSEN)
        // A. Unitomo
        $dosenUnitomo1 = User::firstOrCreate(
            ['email' => 'dosen.unitomo@unitomo.ac.id'],
            [
                'name' => 'Dr. Ir. Bambang Supriyadi, M.Kom',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );
        $dosenUnitomo2 = User::firstOrCreate(
            ['email' => 'dosen.unitomo2@unitomo.ac.id'],
            [
                'name' => 'Dr. Sri Rahayu, S.Kom., M.T',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );

        // B. UNESA
        $dosenUnesa1 = User::firstOrCreate(
            ['email' => 'dosen.unesa@unesa.ac.id'],
            [
                'name' => 'Dr. Erina Nur Azizah, S.Kom., M.Cs',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNESA']->id,
                'university' => $universities['UNESA']->name,
            ]
        );
        $dosenUnesa2 = User::firstOrCreate(
            ['email' => 'dosen.unesa2@unesa.ac.id'],
            [
                'name' => 'Prof. Dr. Agus Widodo, M.Pd',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNESA']->id,
                'university' => $universities['UNESA']->name,
            ]
        );

        // C. ITS
        $dosenIts1 = User::firstOrCreate(
            ['email' => 'dosen.its1@its.ac.id'],
            [
                'name' => 'Prof. Dr. Eng. Chastine Fatichah, S.Kom., M.Kom',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['ITS']->id,
                'university' => $universities['ITS']->name,
            ]
        );
        $dosenIts2 = User::firstOrCreate(
            ['email' => 'dosen.its2@its.ac.id'],
            [
                'name' => 'Dr. R. V. Hari Ginardi, M.Sc',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['ITS']->id,
                'university' => $universities['ITS']->name,
            ]
        );

        // D. UNAIR
        $dosenUnair1 = User::firstOrCreate(
            ['email' => 'dosen.unair1@unair.ac.id'],
            [
                'name' => 'Dr. Rimuljo Hendradi, S.Si., M.Si',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNAIR']->id,
                'university' => $universities['UNAIR']->name,
            ]
        );
        $dosenUnair2 = User::firstOrCreate(
            ['email' => 'dosen.unair2@unair.ac.id'],
            [
                'name' => 'Ira Puspitasari, S.T., M.T., Ph.D',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UNAIR']->id,
                'university' => $universities['UNAIR']->name,
            ]
        );

        // E. UPN Veteran Jawa Timur
        $dosenUpn1 = User::firstOrCreate(
            ['email' => 'dosen.upn@upnjatim.ac.id'],
            [
                'name' => 'Dr. Eng. Yasin Al-Aqsho, S.Kom., M.Kom',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UPN']->id,
                'university' => $universities['UPN']->name,
            ]
        );
        $dosenUpn2 = User::firstOrCreate(
            ['email' => 'dosen.upn2@upnjatim.ac.id'],
            [
                'name' => 'Eva Yulia Puspaningrum, S.Kom., M.Kom',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $universities['UPN']->id,
                'university' => $universities['UPN']->name,
            ]
        );

        // 5. SEED MASTER UNIT KERJA TERSTRUKTUR KETAT PER INSTANSI
        $unitData = [
            // Instansi 1: Diskominfo (Agency ID 1)
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Layanan Informatika & E-Government',
                'description' => 'Pengembangan arsitektur SPBE, integrasi aplikasi layanan publik Pemkot Surabaya, dan portal WargaKu',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)',
                'description' => 'Pusat tanggap insiden siber (CSIRT), implementasi TTE BSrE, enkripsi data, dan audit keamanan sistem informasi',
                'quota' => 6,
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

            // Instansi 2: Dispusip (Agency ID 2)
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital',
                'description' => 'Digitalisasi koleksi naskah kuno, sistem temu kembali arsip digital, dan otomasi perpustakaan daerah',
                'quota' => 6,
            ],
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik',
                'description' => 'Pengelolaan arsip digital dinas, alih media dokumen bersejarah Kota Surabaya, dan repositori arsip elektronik',
                'quota' => 5,
            ],
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Pembinaan & Pengembangan Minat Baca',
                'description' => 'Pemberdayaan taman bacaan masyarakat (TBM), mobil perpustakaan keliling, dan program literasi kota',
                'quota' => 6,
            ],

            // Instansi 3: Dispendukcapil (Agency ID 3)
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pengelolaan Informasi Administrasi Kependudukan (PIAK)',
                'description' => 'Integrasi sistem database kependudukan Klampid New Generation (KNG) dan keamanan data kependudukan',
                'quota' => 6,
            ],
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pelayanan Pendaftaran Penduduk',
                'description' => 'Pelayanan adminduk terintegrasi di kelurahan, kecamatan, dan mall pelayanan publik (MPP) Siola',
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
                [
                    'name' => $uData['name'],
                    'agency_profile_id' => $uData['agency_profile_id'],
                ],
                [
                    'description' => $uData['description'],
                    'quota' => $uData['quota'],
                ]
            );
        }

        // 6. SEED DATA MAHASISWA TESTING (PENDING, VERIFIED, REJECTED)
        // Mahasiswa 1: Raveldo Andyka (UPN - PENDING)
        $mhs1 = User::firstOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            [
                'name' => 'Raveldo Andyka',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UPN']->id,
                'university' => $universities['UPN']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs1->id],
            ['nim' => '22081010001', 'universitas' => $universities['UPN']->name, 'jurusan' => 'Informatika', 'phone' => '081234567890']
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

        // Mahasiswa 2: Dimas Adam (UNAIR - VERIFIED)
        $mhs2 = User::firstOrCreate(
            ['email' => 'dimas.adam@mhs.unair.ac.id'],
            [
                'name' => 'Dimas Adam',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNAIR']->id,
                'university' => $universities['UNAIR']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs2->id],
            ['nim' => '22081010002', 'universitas' => $universities['UNAIR']->name, 'jurusan' => 'Sistem Informasi', 'phone' => '089876543210']
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

        // Mahasiswa 3: Siti Nurhaliza (UNESA - REJECTED)
        $mhs3 = User::firstOrCreate(
            ['email' => 'siti.nurhaliza@mhs.unesa.ac.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNESA']->id,
                'university' => $universities['UNESA']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs3->id],
            ['nim' => '22081010003', 'universitas' => $universities['UNESA']->name, 'jurusan' => 'Teknik Informatika', 'phone' => '087654321098']
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

        // 7. SEED 10 MAHASISWA LULUS DENGAN DISTRIBUSI KAMPUS, UNIT & PEMBIMBING
        $graduatedStudents = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@mhs.unitomo.ac.id',
                'nim' => '22081010011',
                'univ_key' => 'UNITOMO',
                'jurusan' => 'Informatika',
                'phone' => '081234560011',
                'dosen' => $dosenUnitomo1,
            ],
            [
                'name' => 'Nabila Putri Pratama',
                'email' => 'nabila.putri@mhs.unesa.ac.id',
                'nim' => '22081010012',
                'univ_key' => 'UNESA',
                'jurusan' => 'Sistem Informasi',
                'phone' => '081234560012',
                'dosen' => $dosenUnesa1,
            ],
            [
                'name' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@mhs.unitomo.ac.id',
                'nim' => '22081010013',
                'univ_key' => 'UNITOMO',
                'jurusan' => 'Teknik Informatika',
                'phone' => '081234560013',
                'dosen' => $dosenUnitomo2,
            ],
            [
                'name' => 'Anisa Rahmawati',
                'email' => 'anisa.rahma@mhs.unesa.ac.id',
                'nim' => '22081010014',
                'univ_key' => 'UNESA',
                'jurusan' => 'Teknologi Informasi',
                'phone' => '081234560014',
                'dosen' => $dosenUnesa2,
            ],
            [
                'name' => 'Fajar Dwi Santoso',
                'email' => 'fajar.dwi@mhs.its.ac.id',
                'nim' => '22081010015',
                'univ_key' => 'ITS',
                'jurusan' => 'Teknik Informatika',
                'phone' => '081234560015',
                'dosen' => $dosenIts1,
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@mhs.unair.ac.id',
                'nim' => '22081010016',
                'univ_key' => 'UNAIR',
                'jurusan' => 'Sistem Informasi',
                'phone' => '081234560016',
                'dosen' => $dosenUnair1,
            ],
            [
                'name' => 'Bagus Tri Wicaksono',
                'email' => 'bagus.tri@mhs.upnjatim.ac.id',
                'nim' => '22081010017',
                'univ_key' => 'UPN',
                'jurusan' => 'Informatika',
                'phone' => '081234560017',
                'dosen' => $dosenUpn1,
            ],
            [
                'name' => 'Clara Salsabila',
                'email' => 'clara.salsabila@mhs.its.ac.id',
                'nim' => '22081010018',
                'univ_key' => 'ITS',
                'jurusan' => 'Sistem Informasi',
                'phone' => '081234560018',
                'dosen' => $dosenIts2,
            ],
            [
                'name' => 'Hafidz Maulana',
                'email' => 'hafidz.m@mhs.unair.ac.id',
                'nim' => '22081010019',
                'univ_key' => 'UNAIR',
                'jurusan' => 'Teknik Informatika',
                'phone' => '081234560019',
                'dosen' => $dosenUnair2,
            ],
            [
                'name' => 'Putri Maharani',
                'email' => 'putri.maharani@mhs.upnjatim.ac.id',
                'nim' => '22081010020',
                'univ_key' => 'UPN',
                'jurusan' => 'Sains Data',
                'phone' => '081234560020',
                'dosen' => $dosenUpn2,
            ],
        ];

        $startDate = Carbon::now()->subMonths(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $evaluationNotes = [
            'Sangat memuaskan, proaktif, dan menyelesaikan tugas-tugas magang dinas dengan hasil optimal.',
            'Disiplin tinggi, komunikasi tim sangat baik, dan menguasai tools teknis unit kerja dengan cepat.',
            'Inisiatif luar biasa dalam pemecahan masalah serta dokumentasi laporan yang terstruktur rapi.',
            'Kinerja sangat memuaskan, selalu hadir tepat waktu, dan berkontribusi aktif pada project tim.',
            'Mampu bekerja mandiri maupun kolaboratif, hasil kerja berkualitas tinggi dan sesuai target dinas.',
        ];

        foreach ($graduatedStudents as $index => $data) {
            $univModel = $universities[$data['univ_key']];

            // A. Buat User Mahasiswa
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('mahasiswa123'),
                    'role' => 'mahasiswa',
                    'university_id' => $univModel->id,
                    'university' => $univModel->name,
                ]
            );

            // B. Buat Profil Mahasiswa
            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nim' => $data['nim'],
                    'universitas' => $univModel->name,
                    'jurusan' => $data['jurusan'],
                    'phone' => $data['phone'],
                ]
            );

            // C. Distribusi Unit Kerja & Pembimbing Lapangan Dinas Sesuai Instansi Unit
            $assignedUnit = $units[$index % count($units)];
            $agencyMentors = array_values(array_filter($allMentors, function ($m) use ($assignedUnit) {
                return $m->agency_profile_id === $assignedUnit->agency_profile_id;
            }));
            $assignedMentor = $agencyMentors[$index % count($agencyMentors)];
            $assignedDosen = $data['dosen'];

            // D. Buat Application Status ACCEPTED
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

            // E. Buat Placement dengan Pembimbing Dinas & Dosen Pembimbing Kampus
            $placement = Placement::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'mentor_id' => $assignedMentor->id,
                    'academic_advisor_id' => $assignedDosen->id,
                    'pembimbing_id' => $assignedMentor->id,
                ]
            );

            // F. Buat Logbook Kegiatan Magang
            $logActivities = [
                'Melakukan perancangan antarmuka pengguna (UI/UX) dan wireframe portal layanan publik.',
                'Mengembangkan modul otentikasi multi-peran dan integrasi API basis data dinas.',
                'Melakukan pengujian fungsional modul logbook, verifikasi berkas, dan perbaikan bug.',
                'Menyusun dokumentasi teknis sistem, standarisasi TTE BSrE, dan arsitektur database.',
            ];

            foreach ($logActivities as $logIndex => $activityText) {
                Logbook::firstOrCreate(
                    [
                        'placement_id' => $placement->id,
                        'date' => Carbon::now()->subDays(15 - ($logIndex * 3))->format('Y-m-d'),
                    ],
                    [
                        'activity' => $activityText,
                        'attachment' => null,
                        'status' => $logIndex === 0 ? 'approved' : ($logIndex === 3 ? 'pending' : 'approved'),
                        'feedback' => $logIndex === 0 ? 'Kegiatan terverifikasi dengan sangat baik oleh Pembimbing Dinas.' : null,
                    ]
                );
            }

            // G. Buat Laporan Akhir (Approved)
            FinalReport::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'file_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                    'status' => 'approved',
                    'feedback' => 'Laporan akhir magang telah diperiksa, disetujui, dan memenuhi standar instansi & kampus.',
                ]
            );

            // H. Buat Penilaian Evaluasi
            if ($index < 8) {
                $disiplin = rand(88, 96);
                $kinerja = rand(90, 98);
                $laporan = rand(86, 95);
                $akademik = rand(88, 97);
                $catatan = $evaluationNotes[$index % count($evaluationNotes)];

                Evaluation::firstOrCreate(
                    ['placement_id' => $placement->id],
                    [
                        'nilai_disiplin' => $disiplin,
                        'nilai_kinerja' => $kinerja,
                        'nilai_laporan' => $laporan,
                        'nilai_akademik' => $akademik,
                        'catatan' => $catatan,
                        'catatan_dosen' => 'Mahasiswa menunjukkan pemahaman metodologi dan implementasi ilmiah yang sangat komprehensif pada laporan magang.',
                    ]
                );
            }
        }
    }
}