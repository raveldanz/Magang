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
        // =========================================================================
        // 0. SEED MASTER DATA UNIVERSITAS DI SURABAYA
        // =========================================================================
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

        // =========================================================================
        // 1. SEED PROFIL MULTI-INSTANSI RESMI PEMERINTAH KOTA SURABAYA
        // =========================================================================
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

        // =========================================================================
        // 2. SEED USER ADMIN (Utama & Masing-Masing Instansi Pemkot Surabaya)
        // =========================================================================
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

        // =========================================================================
        // 3. SEED 3 AKUN MENTOR PER INSTANSI PEMKOT SURABAYA (TOTAL 9 MENTOR)
        // =========================================================================
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

        // =========================================================================
        // 4. SEED AKUN RESMI PERGURUAN TINGGI (ROLE: UNIVERSITAS)
        // =========================================================================
        $univAccounts = [
            'UNITOMO' => User::firstOrCreate(
                ['email' => 'unitomo@unitomo.ac.id'],
                [
                    'name' => 'Universitas Dr. Soetomo',
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $universities['UNITOMO']->id,
                    'university' => $universities['UNITOMO']->name,
                ]
            ),
            'UNESA' => User::firstOrCreate(
                ['email' => 'unesa@unesa.ac.id'],
                [
                    'name' => 'Universitas Negeri Surabaya',
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $universities['UNESA']->id,
                    'university' => $universities['UNESA']->name,
                ]
            ),
            'ITS' => User::firstOrCreate(
                ['email' => 'its@its.ac.id'],
                [
                    'name' => 'Institut Teknologi Sepuluh Nopember',
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $universities['ITS']->id,
                    'university' => $universities['ITS']->name,
                ]
            ),
            'UNAIR' => User::firstOrCreate(
                ['email' => 'unair@unair.ac.id'],
                [
                    'name' => 'Universitas Airlangga',
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $universities['UNAIR']->id,
                    'university' => $universities['UNAIR']->name,
                ]
            ),
            'UPN' => User::firstOrCreate(
                ['email' => 'upnjatim@upnjatim.ac.id'],
                [
                    'name' => 'UPN Veteran Jawa Timur',
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $universities['UPN']->id,
                    'university' => $universities['UPN']->name,
                ]
            ),
        ];

        // =========================================================================
        // 5. SEED 2 AKUN DOSEN PEMBIMBING LAPANGAN (DPL) PER KAMPUS (TOTAL 10 DOSEN)
        // =========================================================================
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

        // =========================================================================
        // 6. SEED MASTER UNIT KERJA TERSTRUKTUR KETAT PER INSTANSI
        // =========================================================================
        $unitData = [
            // Instansi 1: Diskominfo (Agency ID 1)
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Layanan Informatika & E-Government',
                'description' => 'Pengembangan arsitektur SPBE, integrasi aplikasi layanan publik Pemkot Surabaya, dan portal WargaKu',
                'quota' => 15,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)',
                'description' => 'Pusat tanggap insiden siber (CSIRT), implementasi TTE BSrE, enkripsi data, dan audit keamanan sistem informasi',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Pengelolaan Informasi & Komunikasi Publik',
                'description' => 'Pengelolaan media komunikasi resmi, saluran pengaduan masyarakat, kehumasan, dan keterbukaan informasi publik (PPID)',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[0]->id,
                'name' => 'Bidang Infrastruktur TI & Jaringan Komunikasi',
                'description' => 'Pemeliharaan jaringan fiber optic intra-pemerintah, Data Center Tier-3 Pemkot, cloud infrastructure, dan free wifi publik',
                'quota' => 10,
            ],

            // Instansi 2: Dispusip (Agency ID 2)
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital',
                'description' => 'Digitalisasi koleksi naskah kuno, sistem temu kembali arsip digital, dan otomasi perpustakaan daerah',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik',
                'description' => 'Pengelolaan arsip digital dinas, alih media dokumen bersejarah Kota Surabaya, dan repositori arsip elektronik',
                'quota' => 8,
            ],
            [
                'agency_profile_id' => $agencies[1]->id,
                'name' => 'Bidang Pembinaan & Pengembangan Minat Baca',
                'description' => 'Pemberdayaan taman bacaan masyarakat (TBM), mobil perpustakaan keliling, dan program literasi kota',
                'quota' => 8,
            ],

            // Instansi 3: Dispendukcapil (Agency ID 3)
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pengelolaan Informasi Administrasi Kependudukan (PIAK)',
                'description' => 'Integrasi sistem database kependudukan Klampid New Generation (KNG) dan keamanan data kependudukan',
                'quota' => 10,
            ],
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pelayanan Pendaftaran Penduduk',
                'description' => 'Pelayanan adminduk terintegrasi di kelurahan, kecamatan, dan mall pelayanan publik (MPP) Siola',
                'quota' => 8,
            ],
            [
                'agency_profile_id' => $agencies[2]->id,
                'name' => 'Bidang Pemanfaatan Data & Inovasi Pelayanan Kependudukan',
                'description' => 'Inovasi integrasi data kependudukan dengan BPJS, Dinsos, dan perbankan daerah untuk percepatan layanan publik',
                'quota' => 8,
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

        // =========================================================================
        // 7. SEED AKUN MAHASISWA LIFECYCLE PROGRESS STAGES
        // =========================================================================

        // -------------------------------------------------------------------------
        // A. TAHAP DRAFT (Baru Daftar / Belum Mengajukan Magang)
        // -------------------------------------------------------------------------
        $mhsDraft = User::firstOrCreate(
            ['email' => 'mhs.draft@unitomo.ac.id'],
            [
                'name' => 'Fajar Maulana (Draft)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsDraft->id],
            [
                'nim' => '22081010091',
                'universitas' => $universities['UNITOMO']->name,
                'university_id' => $universities['UNITOMO']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '5',
                'phone' => '081234560091',
                'alamat' => 'Jl. Semolowaru No. 45, Surabaya',
                'address' => 'Jl. Semolowaru No. 45, Surabaya',
                'emergency_contact_name' => 'Bpk. Maulana (Ayah)',
                'emergency_contact_phone' => '081234560090',
            ]
        );

        // -------------------------------------------------------------------------
        // B. TAHAP PENDING (Menunggu Seleksi / Verifikasi Dinas)
        // -------------------------------------------------------------------------
        // Pending 1: Diskominfo (UNESA)
        $mhsPending1 = User::firstOrCreate(
            ['email' => 'mhs.pending1@unesa.ac.id'],
            [
                'name' => 'Bayu Pratama (Pending Kominfo)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNESA']->id,
                'university' => $universities['UNESA']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsPending1->id],
            [
                'nim' => '22081010092',
                'universitas' => $universities['UNESA']->name,
                'university_id' => $universities['UNESA']->id,
                'faculty' => 'Fakultas Teknik',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Teknik Informatika',
                'major' => 'Teknik Informatika',
                'semester' => '5',
                'phone' => '081234560092',
                'alamat' => 'Jl. Ketintang Barat No. 12, Surabaya',
                'address' => 'Jl. Ketintang Barat No. 12, Surabaya',
                'emergency_contact_name' => 'Ibu Pratama (Ibu)',
                'emergency_contact_phone' => '081234560093',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhsPending1->id],
            [
                'unit_id' => $units[0]->id, // Diskominfo - Layanan Informatika
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'pending',
            ]
        );

        // Pending 2: Dispusip (UNAIR)
        $mhsPending2 = User::firstOrCreate(
            ['email' => 'mhs.pending2@unair.ac.id'],
            [
                'name' => 'Sarah Amelia (Pending Dispusip)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNAIR']->id,
                'university' => $universities['UNAIR']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsPending2->id],
            [
                'nim' => '22081010093',
                'universitas' => $universities['UNAIR']->name,
                'university_id' => $universities['UNAIR']->id,
                'faculty' => 'Fakultas Sains dan Teknologi',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '5',
                'phone' => '081234560094',
                'alamat' => 'Jl. Mulyorejo Utara No. 20, Surabaya',
                'address' => 'Jl. Mulyorejo Utara No. 20, Surabaya',
                'emergency_contact_name' => 'Bpk. Amelia (Ayah)',
                'emergency_contact_phone' => '081234560095',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhsPending2->id],
            [
                'unit_id' => $units[4]->id, // Dispusip - Otomasi Perpustakaan
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'pending',
            ]
        );

        // -------------------------------------------------------------------------
        // C. TAHAP DITERIMA BELUM PILIH DPL (Accepted - Need DPL Selection)
        // -------------------------------------------------------------------------
        // Accepted 1: Diskominfo (UNITOMO)
        $mhsAccepted1 = User::firstOrCreate(
            ['email' => 'mhs.accepted1@unitomo.ac.id'],
            [
                'name' => 'Aldi Firmansyah (Accepted No DPL)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsAccepted1->id],
            [
                'nim' => '22081010094',
                'universitas' => $universities['UNITOMO']->name,
                'university_id' => $universities['UNITOMO']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Teknik Informatika',
                'major' => 'Teknik Informatika',
                'semester' => '5',
                'phone' => '081234560096',
                'alamat' => 'Jl. Nginden Semolo No. 88, Surabaya',
                'address' => 'Jl. Nginden Semolo No. 88, Surabaya',
                'emergency_contact_name' => 'Bpk. Firmansyah (Ayah)',
                'emergency_contact_phone' => '081234560097',
            ]
        );
        $appAcc1 = Application::firstOrCreate(
            ['user_id' => $mhsAccepted1->id],
            [
                'unit_id' => $units[1]->id, // Diskominfo - CSIRT
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/081/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            ]
        );
        Placement::firstOrCreate(
            ['application_id' => $appAcc1->id],
            [
                'mentor_id' => $mentorsDiskominfo[0]->id,
                'pembimbing_id' => $mentorsDiskominfo[0]->id,
                'academic_advisor_id' => null, // Belum memilih DPL Kampus
            ]
        );

        // Accepted 2: Dispendukcapil (ITS)
        $mhsAccepted2 = User::firstOrCreate(
            ['email' => 'mhs.accepted2@its.ac.id'],
            [
                'name' => 'Nanda Kartika (Accepted No DPL)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['ITS']->id,
                'university' => $universities['ITS']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsAccepted2->id],
            [
                'nim' => '22081010095',
                'universitas' => $universities['ITS']->name,
                'university_id' => $universities['ITS']->id,
                'faculty' => 'Fakultas Teknologi Elektro dan Informatika Cerdas',
                'fakultas' => 'Fakultas Teknologi Elektro dan Informatika Cerdas',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '5',
                'phone' => '081234560098',
                'alamat' => 'Jl. Gebang Wetan No. 15, Surabaya',
                'address' => 'Jl. Gebang Wetan No. 15, Surabaya',
                'emergency_contact_name' => 'Ibu Kartika (Ibu)',
                'emergency_contact_phone' => '081234560099',
            ]
        );
        $appAcc2 = Application::firstOrCreate(
            ['user_id' => $mhsAccepted2->id],
            [
                'unit_id' => $units[7]->id, // Dispendukcapil - PIAK
                'start_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/082/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            ]
        );
        Placement::firstOrCreate(
            ['application_id' => $appAcc2->id],
            [
                'mentor_id' => $mentorsDispendukcapil[0]->id,
                'pembimbing_id' => $mentorsDispendukcapil[0]->id,
                'academic_advisor_id' => null, // Belum memilih DPL Kampus
            ]
        );

        // -------------------------------------------------------------------------
        // D. TAHAP AKTIF MAGANG (Active - Mengisi Logbook & Pembimbing Lengkap)
        // -------------------------------------------------------------------------
        // Aktif 1: Diskominfo (UNITOMO) - 5 Logbook Variatif
        $mhsAktif1 = User::firstOrCreate(
            ['email' => 'mhs.aktif1@unitomo.ac.id'],
            [
                'name' => 'Bima Arya (Aktif Kominfo)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsAktif1->id],
            [
                'nim' => '22081010096',
                'universitas' => $universities['UNITOMO']->name,
                'university_id' => $universities['UNITOMO']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '6',
                'phone' => '081234560101',
                'alamat' => 'Jl. Manyar Rejo No. 34, Surabaya',
                'address' => 'Jl. Manyar Rejo No. 34, Surabaya',
                'emergency_contact_name' => 'Bpk. Arya (Ayah)',
                'emergency_contact_phone' => '081234560100',
            ]
        );
        $appAktif1 = Application::firstOrCreate(
            ['user_id' => $mhsAktif1->id],
            [
                'unit_id' => $units[0]->id, // Diskominfo
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/071/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(35)->format('Y-m-d'),
            ]
        );
        $placementAktif1 = Placement::firstOrCreate(
            ['application_id' => $appAktif1->id],
            [
                'mentor_id' => $mentorsDiskominfo[0]->id, // mentor.kominfo1
                'pembimbing_id' => $mentorsDiskominfo[0]->id,
                'academic_advisor_id' => $dosenUnitomo1->id, // dosen.unitomo
            ]
        );

        $logbooksAktif1 = [
            [
                'date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'activity' => 'Melakukan analisis kebutuhan antarmuka dashboard monitoring SPBE Pemkot Surabaya.',
                'status' => 'approved',
                'feedback' => 'Analisis komprehensif dan selaras dengan blueprint arsitektur SPBE.',
                'lecturer_status' => 'approved',
                'lecturer_feedback' => 'Topik relevan dengan mata kuliah RPL & Rekayasa Perangkat Lunak.',
                'lecturer_verified_at' => Carbon::now()->subDays(19),
            ],
            [
                'date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'activity' => 'Implementasi modul autentikasi single sign-on (SSO) dan token security.',
                'status' => 'approved',
                'feedback' => 'Implementasi security token telah diuji dan memenuhi standar CSIRT.',
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
            [
                'date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'activity' => 'Melakukan integrasi REST API data layanan kependudukan dan validasi data dinas.',
                'status' => 'pending',
                'feedback' => null,
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
            [
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'activity' => 'Menyusun dokumentasi teknis OpenAPI dan endpoint swagger sistem dinas.',
                'status' => 'rejected',
                'feedback' => 'Mohon tambahkan contoh response format JSON error handling pada dokumen swagger.',
                'lecturer_status' => 'rejected',
                'lecturer_feedback' => 'Tolong lengkapi perbaikan swagger sebelum melanjutkan ke modul berikutnya.',
                'lecturer_verified_at' => Carbon::now()->subDays(4),
            ],
            [
                'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'activity' => 'Memperbaiki dokumentasi swagger dan pengujian stress-testing API.',
                'status' => 'approved',
                'feedback' => 'Perbaikan diterima dengan sangat baik.',
                'lecturer_status' => 'approved',
                'lecturer_feedback' => 'ACC Dosen: Modul dokumentasi teruji valid.',
                'lecturer_verified_at' => Carbon::now(),
            ],
        ];

        foreach ($logbooksAktif1 as $lbData) {
            Logbook::firstOrCreate(
                [
                    'placement_id' => $placementAktif1->id,
                    'date' => $lbData['date'],
                ],
                $lbData
            );
        }

        // Aktif 2: Dispusip (UNESA) - 3 Logbook
        $mhsAktif2 = User::firstOrCreate(
            ['email' => 'mhs.aktif2@unesa.ac.id'],
            [
                'name' => 'Dinda Kirana (Aktif Dispusip)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNESA']->id,
                'university' => $universities['UNESA']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsAktif2->id],
            [
                'nim' => '22081010097',
                'universitas' => $universities['UNESA']->name,
                'university_id' => $universities['UNESA']->id,
                'faculty' => 'Fakultas Teknik',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234560102',
                'alamat' => 'Jl. Lidah Wetan No. 50, Surabaya',
                'address' => 'Jl. Lidah Wetan No. 50, Surabaya',
                'emergency_contact_name' => 'Bpk. Kirana (Ayah)',
                'emergency_contact_phone' => '081234560103',
            ]
        );
        $appAktif2 = Application::firstOrCreate(
            ['user_id' => $mhsAktif2->id],
            [
                'unit_id' => $units[4]->id, // Dispusip
                'start_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(65)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/072/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
            ]
        );
        $placementAktif2 = Placement::firstOrCreate(
            ['application_id' => $appAktif2->id],
            [
                'mentor_id' => $mentorsDispusip[0]->id, // mentor.dispusip1
                'pembimbing_id' => $mentorsDispusip[0]->id,
                'academic_advisor_id' => $dosenUnesa1->id, // dosen.unesa
            ]
        );
        $logbooksAktif2 = [
            [
                'date' => Carbon::now()->subDays(18)->format('Y-m-d'),
                'activity' => 'Digitalisasi dan penataan metadata naskah kuno perpustakaan daerah.',
                'status' => 'approved',
                'feedback' => 'Proses digitalisasi rapi dan sesuai kaidah preservasi.',
                'lecturer_status' => 'approved',
                'lecturer_feedback' => 'Kegiatan selaras dengan mata kuliah Manajemen Basis Data.',
                'lecturer_verified_at' => Carbon::now()->subDays(17),
            ],
            [
                'date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'activity' => 'Penyusunan modul temu kembali arsip statis berbasis web.',
                'status' => 'approved',
                'feedback' => 'Fitur pencarian full-text indexing berjalan cepat.',
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
            [
                'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'activity' => 'Uji coba sistem otomasi barcode sirkulasi buku.',
                'status' => 'pending',
                'feedback' => null,
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
        ];
        foreach ($logbooksAktif2 as $lbData) {
            Logbook::firstOrCreate(
                ['placement_id' => $placementAktif2->id, 'date' => $lbData['date']],
                $lbData
            );
        }

        // Aktif 3: Dispendukcapil (UPN) - 3 Logbook
        $mhsAktif3 = User::firstOrCreate(
            ['email' => 'mhs.aktif3@upnjatim.ac.id'],
            [
                'name' => 'Reza Aditya (Aktif Dukcapil)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UPN']->id,
                'university' => $universities['UPN']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsAktif3->id],
            [
                'nim' => '22081010098',
                'universitas' => $universities['UPN']->name,
                'university_id' => $universities['UPN']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '6',
                'phone' => '081234560104',
                'alamat' => 'Jl. Medokan Asri No. 18, Surabaya',
                'address' => 'Jl. Medokan Asri No. 18, Surabaya',
                'emergency_contact_name' => 'Bpk. Aditya (Ayah)',
                'emergency_contact_phone' => '081234560105',
            ]
        );
        $appAktif3 = Application::firstOrCreate(
            ['user_id' => $mhsAktif3->id],
            [
                'unit_id' => $units[7]->id, // Dispendukcapil
                'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(70)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/073/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
            ]
        );
        $placementAktif3 = Placement::firstOrCreate(
            ['application_id' => $appAktif3->id],
            [
                'mentor_id' => $mentorsDispendukcapil[0]->id, // mentor.dukcapil1
                'pembimbing_id' => $mentorsDispendukcapil[0]->id,
                'academic_advisor_id' => $dosenUpn1->id, // dosen.upn
            ]
        );
        $logbooksAktif3 = [
            [
                'date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'activity' => 'Analisis struktur basis data adminduk Klampid New Generation (KNG).',
                'status' => 'approved',
                'feedback' => 'Analisis data kependudukan sangat teliti.',
                'lecturer_status' => 'approved',
                'lecturer_feedback' => 'Pertahankan akurasi dan pemenuhan regulasi keamanan data.',
                'lecturer_verified_at' => Carbon::now()->subDays(14),
            ],
            [
                'date' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'activity' => 'Pembersihan dan deduplikasi anomali data NIK ganda.',
                'status' => 'approved',
                'feedback' => 'Deduplikasi data sukses membersihkan 250 record anomali.',
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
            [
                'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'activity' => 'Pengembangan dashboard visualisasi demografi kependudukan kecamatan.',
                'status' => 'pending',
                'feedback' => null,
                'lecturer_status' => 'pending',
                'lecturer_feedback' => null,
                'lecturer_verified_at' => null,
            ],
        ];
        foreach ($logbooksAktif3 as $lbData) {
            Logbook::firstOrCreate(
                ['placement_id' => $placementAktif3->id, 'date' => $lbData['date']],
                $lbData
            );
        }

        // -------------------------------------------------------------------------
        // E. TAHAP LULUS (Completed - E-Sertifikat Terbit & Nilai Lengkap)
        // -------------------------------------------------------------------------
        // Lulus 1: Diskominfo (UNITOMO) - Nilai 92.5 (A)
        $mhsLulus1 = User::firstOrCreate(
            ['email' => 'mhs.lulus1@unitomo.ac.id'],
            [
                'name' => 'Hendra Kusuma (Lulus Kominfo)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNITOMO']->id,
                'university' => $universities['UNITOMO']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsLulus1->id],
            [
                'nim' => '22081010099',
                'universitas' => $universities['UNITOMO']->name,
                'university_id' => $universities['UNITOMO']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '6',
                'phone' => '081234560106',
                'alamat' => 'Jl. Gubeng Kertajaya No. 10, Surabaya',
                'address' => 'Jl. Gubeng Kertajaya No. 10, Surabaya',
                'emergency_contact_name' => 'Bpk. Kusuma (Ayah)',
                'emergency_contact_phone' => '081234560107',
            ]
        );
        $appLulus1 = Application::firstOrCreate(
            ['user_id' => $mhsLulus1->id],
            [
                'unit_id' => $units[0]->id, // Diskominfo
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/001/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->subDays(5)->format('Y-m-d'),
            ]
        );
        $placementLulus1 = Placement::firstOrCreate(
            ['application_id' => $appLulus1->id],
            [
                'mentor_id' => $mentorsDiskominfo[0]->id,
                'pembimbing_id' => $mentorsDiskominfo[0]->id,
                'academic_advisor_id' => $dosenUnitomo1->id,
            ]
        );
        for ($i = 0; $i < 4; $i++) {
            Logbook::firstOrCreate(
                [
                    'placement_id' => $placementLulus1->id,
                    'date' => Carbon::now()->subDays(60 - ($i * 15))->format('Y-m-d'),
                ],
                [
                    'activity' => "Pengembangan dan optimalisasi sistem informasi dinas tahap " . ($i + 1),
                    'status' => 'approved',
                    'feedback' => 'Tugas diselesaikan dengan sangat baik dan memenuhi standar.',
                    'lecturer_status' => 'approved',
                    'lecturer_feedback' => 'ACC Dosen: Pelaksanaan magang sangat memuaskan.',
                    'lecturer_verified_at' => Carbon::now()->subDays(58 - ($i * 15)),
                ]
            );
        }
        FinalReport::firstOrCreate(
            ['placement_id' => $placementLulus1->id],
            [
                'file_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                'final_report_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                'status' => 'approved',
                'feedback' => 'Laporan akhir magang lengkap, teruji, dan disetujui untuk penerbitan E-Sertifikat.',
            ]
        );
        Evaluation::firstOrCreate(
            ['placement_id' => $placementLulus1->id],
            [
                'nilai_disiplin' => 93,
                'nilai_kinerja' => 93,
                'nilai_laporan' => 92,
                'nilai_akademik' => 93,
                'catatan' => 'Sangat berdedikasi, inisiatif tinggi, dan menghasilkan kontribusi nyata pada layanan TI Diskominfo Surabaya.',
                'catatan_dosen' => 'Mahasiswa menunjukkan kedisiplinan dan integrasi keilmuan informatika yang sangat unggul.',
            ]
        );

        // Lulus 2: Dispusip (UNAIR) - Nilai 90.0 (A)
        $mhsLulus2 = User::firstOrCreate(
            ['email' => 'mhs.lulus2@unair.ac.id'],
            [
                'name' => 'Tiara Maharani (Lulus Dispusip)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $universities['UNAIR']->id,
                'university' => $universities['UNAIR']->name,
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhsLulus2->id],
            [
                'nim' => '22081010100',
                'universitas' => $universities['UNAIR']->name,
                'university_id' => $universities['UNAIR']->id,
                'faculty' => 'Fakultas Sains dan Teknologi',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234560108',
                'alamat' => 'Jl. Dharmahusada Indah No. 25, Surabaya',
                'address' => 'Jl. Dharmahusada Indah No. 25, Surabaya',
                'emergency_contact_name' => 'Bpk. Maharani (Ayah)',
                'emergency_contact_phone' => '081234560109',
            ]
        );
        $appLulus2 = Application::firstOrCreate(
            ['user_id' => $mhsLulus2->id],
            [
                'unit_id' => $units[4]->id, // Dispusip
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/002/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->subDays(5)->format('Y-m-d'),
            ]
        );
        $placementLulus2 = Placement::firstOrCreate(
            ['application_id' => $appLulus2->id],
            [
                'mentor_id' => $mentorsDispusip[0]->id,
                'pembimbing_id' => $mentorsDispusip[0]->id,
                'academic_advisor_id' => $dosenUnair1->id,
            ]
        );
        for ($i = 0; $i < 4; $i++) {
            Logbook::firstOrCreate(
                [
                    'placement_id' => $placementLulus2->id,
                    'date' => Carbon::now()->subDays(60 - ($i * 15))->format('Y-m-d'),
                ],
                [
                    'activity' => "Implementasi sistem perpustakaan dan repositori digital tahap " . ($i + 1),
                    'status' => 'approved',
                    'feedback' => 'Hasil implementasi memuaskan dan tervalidasi.',
                    'lecturer_status' => 'approved',
                    'lecturer_feedback' => 'ACC Dosen: Implementasi sistem sangat rapi.',
                    'lecturer_verified_at' => Carbon::now()->subDays(58 - ($i * 15)),
                ]
            );
        }
        FinalReport::firstOrCreate(
            ['placement_id' => $placementLulus2->id],
            [
                'file_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                'final_report_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                'status' => 'approved',
                'feedback' => 'Laporan akhir magang lengkap dan disetujui.',
            ]
        );
        Evaluation::firstOrCreate(
            ['placement_id' => $placementLulus2->id],
            [
                'nilai_disiplin' => 90,
                'nilai_kinerja' => 90,
                'nilai_laporan' => 90,
                'nilai_akademik' => 90,
                'catatan' => 'Kerja sama tim sangat solid dan berorientasi hasil optimal.',
                'catatan_dosen' => 'Laporan magang terstruktur rapi dan metodologi tepat.',
            ]
        );

        // -------------------------------------------------------------------------
        // F. AKUN UTAMA TESTING
        // -------------------------------------------------------------------------
        // 1. Raveldo Andyka (UPN - PENDING)
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
            [
                'nim' => '22081010001',
                'universitas' => $universities['UPN']->name,
                'university_id' => $universities['UPN']->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '5',
                'phone' => '081234567890',
                'alamat' => 'Jl. Rungkut Asri Timur No. 10, Surabaya',
                'address' => 'Jl. Rungkut Asri Timur No. 10, Surabaya',
                'emergency_contact_name' => 'Bpk. Andyka (Orang Tua)',
                'emergency_contact_phone' => '081234567899',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhs1->id],
            [
                'unit_id' => $units[0]->id,
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'pending',
            ]
        );

        // 2. Dimas Adam (UNAIR - VERIFIED)
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
            [
                'nim' => '22081010002',
                'universitas' => $universities['UNAIR']->name,
                'university_id' => $universities['UNAIR']->id,
                'faculty' => 'Fakultas Sains dan Teknologi',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '5',
                'phone' => '089876543210',
                'alamat' => 'Jl. Mulyorejo No. 45, Surabaya',
                'address' => 'Jl. Mulyorejo No. 45, Surabaya',
                'emergency_contact_name' => 'Ibu Ratna (Ibu)',
                'emergency_contact_phone' => '089876543299',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhs2->id],
            [
                'unit_id' => $units[1]->id,
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'verified',
            ]
        );

        // 3. Siti Nurhaliza (UNESA - REJECTED)
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
            [
                'nim' => '22081010003',
                'universitas' => $universities['UNESA']->name,
                'university_id' => $universities['UNESA']->id,
                'faculty' => 'Fakultas Teknik',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Teknik Informatika',
                'major' => 'Teknik Informatika',
                'semester' => '5',
                'phone' => '087654321098',
                'alamat' => 'Jl. Ketintang Baru No. 8, Surabaya',
                'address' => 'Jl. Ketintang Baru No. 8, Surabaya',
                'emergency_contact_name' => 'Bpk. Nurhalim (Ayah)',
                'emergency_contact_phone' => '087654321000',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhs3->id],
            [
                'unit_id' => $units[3]->id,
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'rejected',
                'rejection_note' => 'Dokumen Portofolio dan Transkrip Nilai belum terlampir dengan jelas. Silakan ajukan ulang berkas yang lengkap.',
            ]
        );

        // -------------------------------------------------------------------------
        // G. SEED 10 MAHASISWA LULUS DENGAN DISTRIBUSI KAMPUS, UNIT & PEMBIMBING
        // -------------------------------------------------------------------------
        $graduatedStudents = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@mhs.unitomo.ac.id',
                'nim' => '22081010011',
                'univ_key' => 'UNITOMO',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'semester' => '6',
                'phone' => '081234560011',
                'dosen' => $dosenUnitomo1,
            ],
            [
                'name' => 'Nabila Putri Pratama',
                'email' => 'nabila.putri@mhs.unesa.ac.id',
                'nim' => '22081010012',
                'univ_key' => 'UNESA',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234560012',
                'dosen' => $dosenUnesa1,
            ],
            [
                'name' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@mhs.unitomo.ac.id',
                'nim' => '22081010013',
                'univ_key' => 'UNITOMO',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Teknik Informatika',
                'semester' => '6',
                'phone' => '081234560013',
                'dosen' => $dosenUnitomo2,
            ],
            [
                'name' => 'Anisa Rahmawati',
                'email' => 'anisa.rahma@mhs.unesa.ac.id',
                'nim' => '22081010014',
                'univ_key' => 'UNESA',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Teknologi Informasi',
                'semester' => '6',
                'phone' => '081234560014',
                'dosen' => $dosenUnesa2,
            ],
            [
                'name' => 'Fajar Dwi Santoso',
                'email' => 'fajar.dwi@mhs.its.ac.id',
                'nim' => '22081010015',
                'univ_key' => 'ITS',
                'fakultas' => 'Fakultas Teknologi Elektro dan Informatika Cerdas',
                'jurusan' => 'Teknik Informatika',
                'semester' => '6',
                'phone' => '081234560015',
                'dosen' => $dosenIts1,
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@mhs.unair.ac.id',
                'nim' => '22081010016',
                'univ_key' => 'UNAIR',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'jurusan' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234560016',
                'dosen' => $dosenUnair1,
            ],
            [
                'name' => 'Bagus Tri Wicaksono',
                'email' => 'bagus.tri@mhs.upnjatim.ac.id',
                'nim' => '22081010017',
                'univ_key' => 'UPN',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'semester' => '6',
                'phone' => '081234560017',
                'dosen' => $dosenUpn1,
            ],
            [
                'name' => 'Clara Salsabila',
                'email' => 'clara.salsabila@mhs.its.ac.id',
                'nim' => '22081010018',
                'univ_key' => 'ITS',
                'fakultas' => 'Fakultas Teknologi Elektro dan Informatika Cerdas',
                'jurusan' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234560018',
                'dosen' => $dosenIts2,
            ],
            [
                'name' => 'Hafidz Maulana',
                'email' => 'hafidz.m@mhs.unair.ac.id',
                'nim' => '22081010019',
                'univ_key' => 'UNAIR',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'jurusan' => 'Teknik Informatika',
                'semester' => '6',
                'phone' => '081234560019',
                'dosen' => $dosenUnair2,
            ],
            [
                'name' => 'Putri Maharani',
                'email' => 'putri.maharani@mhs.upnjatim.ac.id',
                'nim' => '22081010020',
                'univ_key' => 'UPN',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Sains Data',
                'semester' => '6',
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
                    'password' => Hash::make('password'),
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
                    'university_id' => $univModel->id,
                    'faculty' => $data['fakultas'],
                    'fakultas' => $data['fakultas'],
                    'jurusan' => $data['jurusan'],
                    'major' => $data['jurusan'],
                    'semester' => $data['semester'],
                    'phone' => $data['phone'],
                    'alamat' => 'Jl. Dharmawangsa No. ' . ($index + 10) . ', Surabaya',
                    'address' => 'Jl. Dharmawangsa No. ' . ($index + 10) . ', Surabaya',
                    'emergency_contact_name' => 'Wali Mahasiswa ' . $data['name'],
                    'emergency_contact_phone' => '0812998877' . str_pad($index, 2, '0', STR_PAD_LEFT),
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
                    'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                    'cv_path' => 'documents/applications/sample_cv.pdf',
                    'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                    'id_card_path' => 'documents/applications/sample_ktm.pdf',
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
                        'lecturer_status' => $logIndex === 0 ? 'approved' : ($logIndex === 3 ? 'pending' : 'approved'),
                        'lecturer_feedback' => $logIndex === 0 ? 'Aktivitas sangat relevan dengan kompetensi kurikulum program studi.' : null,
                        'lecturer_verified_at' => $logIndex === 0 ? Carbon::now()->subDays(14) : null,
                    ]
                );
            }

            // G. Buat Laporan Akhir (Approved)
            FinalReport::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'file_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                    'final_report_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
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