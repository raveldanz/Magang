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
        // 0. Seed Profil Instansi Default
        AgencyProfile::firstOrCreate(
            ['id' => 1],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'agency_name' => 'Dinas Komunikasi Dan Informatika',
                'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'diskominfo@surabaya.go.id',
                'website' => 'https://diskominfo.surabaya.go.id',
                'logo' => 'images/logos/7RAcyORc2Ze01RNCwhpqaWWhTYU5Hh3mU06AFGEF.jpg',
                'signee_name' => 'Drs. H. M. NASER, M.Si',
                'signee_nip' => '19700101 199503 1 002',
                'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                'city' => 'Surabaya',
            ]
        );

        // 1. Seed User Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // 2. Seed User Pembimbing Lapangan
        $pembimbing = User::firstOrCreate(
            ['email' => 'retnomumpuni.if@upnjatim.ac.id'],
            [
                'name' => 'Retno Mumpuni, S.Kom., M.Sc',
                'password' => Hash::make('pembimbing123'),
                'role' => 'pembimbing',
            ]
        );

        // 3. Seed Data Unit Kerja Instansi
        $units = [
            Unit::firstOrCreate(
                ['name' => 'Bidang Aptika & E-Government'], 
                ['description' => 'Pengembangan aplikasi dan tata kelola e-government', 'quota' => 10]
            ),
            Unit::firstOrCreate(
                ['name' => 'Bidang Informasi & Komunikasi Publik'], 
                ['description' => 'Manajemen kehumasan dan saluran informasi publik', 'quota' => 8]
            ),
            Unit::firstOrCreate(
                ['name' => 'Sekretariat & Keuangan'], 
                ['description' => 'Pengelolaan administrasi dan keuangan instansi', 'quota' => 6]
            ),
            Unit::firstOrCreate(
                ['name' => 'Bidang Infrastruktur & Jaringan'], 
                ['description' => 'Pemeliharaan jaringan, server, dan infrastruktur IT', 'quota' => 8]
            ),
            Unit::firstOrCreate(
                ['name' => 'Bidang Persandian & Keamanan Informasi'], 
                ['description' => 'Proteksi cyber, enkripsi, dan keamanan data', 'quota' => 5]
            ),
            Unit::firstOrCreate(
                ['name' => 'Bidang Statistik & Data Analitik'], 
                ['description' => 'Pengolahan data statistik dan integrasi sistem data', 'quota' => 6]
            ),
        ];

        // 4. Seed User Mahasiswa Dasar (Contoh Akun Testing)
        $mhs1 = User::firstOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            [
                'name' => 'Raveldo Andyka',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs1->id],
            [
                'nim' => '22081010001',
                'universitas' => 'UPN Veteran Jawa Timur',
                'jurusan' => 'Informatika',
                'phone' => '081234567890',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhs1->id, 'unit_id' => $units[0]->id],
            [
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'pending',
            ]
        );

        $mhs2 = User::firstOrCreate(
            ['email' => 'AdamGanteng@gmail.com'],
            [
                'name' => 'Dimas Adam',
                'password' => Hash::make('josjis'),
                'role' => 'mahasiswa',
            ]
        );
        StudentProfile::firstOrCreate(
            ['user_id' => $mhs2->id],
            [
                'nim' => '22081010002',
                'universitas' => 'Universitas Airlangga',
                'jurusan' => 'Sistem Informasi',
                'phone' => '089876543210',
            ]
        );
        Application::firstOrCreate(
            ['user_id' => $mhs2->id, 'unit_id' => $units[1]->id],
            [
                'start_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'accepted',
                'letter_number' => '500.12.2/102/436.7.14/' . date('Y'),
                'letter_date' => Carbon::now()->format('Y-m-d'),
            ]
        );

        // 5. SEED 10 MAHASISWA BARU YANG TELAH LULUS MAGANG (UNTUK UJI COBA E-SERTIFIKAT)
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

            // C. Pilih unit secara bergantian
            $assignedUnit = $units[$index % count($units)];

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

            // E. Buat Placement dengan Pembimbing Ibu Retno Mumpuni
            $placement = Placement::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'pembimbing_id' => $pembimbing->id,
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

            // G. Buat Penilaian Evaluasi (Nilai 80 - 95)
            $disiplin = rand(82, 95);
            $kinerja = rand(85, 95);
            $laporan = rand(80, 94);

            Evaluation::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'nilai_disiplin' => $disiplin,
                    'nilai_kinerja' => $kinerja,
                    'nilai_laporan' => $laporan,
                    'catatan' => 'Sangat memuaskan, proaktif, dan menyelesaikan tugas-tugas magang dengan hasil optimal.',
                ]
            );
        }
    }
}