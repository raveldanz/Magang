<?php

namespace Database\Seeders;

use App\Models\AgencyProfile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SurabayaAgenciesSeeder extends Seeder
{
    /**
     * Seeder Master Data Resmi Instansi Dinas & Badan Daerah (OPD) Pemerintah Kota Surabaya.
     * Idempoten, menjaga foreign key penempatan/unit kerja yang sudah ada tanpa duplikasi ID.
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('agency_profiles', 'id'), COALESCE((SELECT MAX(id) FROM agency_profiles), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('units', 'id'), COALESCE((SELECT MAX(id) FROM units), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))");
        }

        $agencies = [
            // =====================================================================
            // A. DINAS DAERAH KOTA SURABAYA
            // =====================================================================
            [
                'match_keyword' => 'Komunikasi',
                'agency_name' => 'Dinas Komunikasi dan Informatika',
                'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'diskominfo@surabaya.go.id',
                'website' => 'https://diskominfo.surabaya.go.id',
                'logo' => 'images/logos/diskominfo.png',
                'signee_name' => 'Drs. H. M. NASER, M.Si',
                'signee_nip' => '19700101 199503 1 002',
                'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                'admin_email' => 'admin.diskominfo@surabaya.go.id',
                'admin_name' => 'Admin Diskominfo Surabaya',
                'units' => [
                    ['name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)', 'quota' => 10, 'description' => 'Operasional keamanan siber, audit kerentanan, dan tanggap insiden keamanan informasi.'],
                    ['name' => 'Bidang Layanan Informatika & E-Government', 'quota' => 15, 'description' => 'Pengembangan arsitektur SPBE, integrasi API, dan rekayasa perangkat lunak layanan publik.'],
                    ['name' => 'Bidang Pengelolaan Informasi & Komunikasi Publik', 'quota' => 10, 'description' => 'Produksi konten media digital, jurnalistik pemerintahan, dan diseminasi informasi publik.'],
                    ['name' => 'Bidang Infrastruktur TI & Jaringan Komunikasi', 'quota' => 10, 'description' => 'Pengelolaan pusat data (Data Center), fiber optic perkotaan, dan infrastruktur cloud Pemkot.'],
                ],
            ],
            [
                'match_keyword' => 'Perpustakaan',
                'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
                'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
                'phone' => '(031) 8707682',
                'email' => 'dispusip@surabaya.go.id',
                'website' => 'https://dispusip.surabaya.go.id',
                'logo' => 'images/logos/dispusip.png',
                'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
                'signee_nip' => '19720315 199803 2 004',
                'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                'admin_email' => 'admin.dispusip@surabaya.go.id',
                'admin_name' => 'Admin Dispusip Surabaya',
                'units' => [
                    ['name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital', 'quota' => 10, 'description' => 'Pengelolaan katalog digital, automasi perpustakaan, dan kurasi literasi digital.'],
                    ['name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik', 'quota' => 8, 'description' => 'Digitalisasi naskah kuno, restorasi arsip bersejarah, dan kearsipan elektronik.'],
                    ['name' => 'Bidang Pembinaan & Pengembangan Minat Baca', 'quota' => 8, 'description' => 'Edukasi literasi masyarakat, mobil perpustakaan keliling, dan kemitraan literasi publik.'],
                ],
            ],
            [
                'match_keyword' => 'Kependudukan',
                'agency_name' => 'Dinas Kependudukan dan Pencatatan Sipil',
                'address' => 'Jl. Manyar Kertoarjo No. 1, Manyar Sabrangan, Kec. Mulyorejo, Kota Surabaya, Jawa Timur 60116',
                'phone' => '(031) 5913166',
                'email' => 'dispendukcapil@surabaya.go.id',
                'website' => 'https://dispendukcapil.surabaya.go.id',
                'logo' => 'images/logos/dispendukcapil.png',
                'signee_name' => 'Eddy Christijanto, S.Sos., M.Si.',
                'signee_nip' => '19680512 199003 1 007',
                'signee_position' => 'Kepala Dinas Kependudukan dan Pencatatan Sipil',
                'admin_email' => 'admin.dispendukcapil@surabaya.go.id',
                'admin_name' => 'Admin Dispendukcapil Surabaya',
                'units' => [
                    ['name' => 'Bidang Pengelolaan Informasi Administrasi Kependudukan (PIAK)', 'quota' => 10, 'description' => 'Integrasi database SIAK, verifikasi biometrik KTP elektronik, dan keamanan data kependudukan.'],
                    ['name' => 'Bidang Pelayanan Pendaftaran Penduduk', 'quota' => 8, 'description' => 'Layanan administrasi kependudukan online (Klampid New Generation) dan pencatatan biodata warga.'],
                    ['name' => 'Bidang Pemanfaatan Data & Inovasi Pelayanan Kependudukan', 'quota' => 8, 'description' => 'Inovasi integrasi data kependudukan lintas instansi untuk bantuan sosial dan layanan publik.'],
                ],
            ],
            [
                'match_keyword' => 'Pendidikan',
                'agency_name' => 'Dinas Pendidikan',
                'address' => 'Jl. Jagir Wonokromo No. 354-356, Sidosermo, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60239',
                'phone' => '(031) 8499515',
                'email' => 'dispendik@surabaya.go.id',
                'website' => 'https://dispendik.surabaya.go.id',
                'logo' => 'images/logos/dispendik.png',
                'signee_name' => 'Yusuf Masruh, S.Pd., M.M.',
                'signee_nip' => '19670615 199203 1 010',
                'signee_position' => 'Kepala Dinas Pendidikan',
                'admin_email' => 'admin.dispendik@surabaya.go.id',
                'admin_name' => 'Admin Dispendik Surabaya',
                'units' => [
                    ['name' => 'Bidang Sekolah Dasar dan Menengah', 'quota' => 10, 'description' => 'Manajemen kurikulum, asesmen kompetensi sekolah, dan pembinaan mutu pendidikan dasar.'],
                    ['name' => 'Bidang Guru dan Tenaga Kependidikan', 'quota' => 8, 'description' => 'Pengembangan kapasitas pendidik, sertifikasi guru, dan administrasi kepegawaian sekolah.'],
                    ['name' => 'Bidang PAUD dan Pendidikan Nonformal', 'quota' => 6, 'description' => 'Pengawasan pendidikan anak usia dini dan program kesetaraan pendidikan masyarakat.'],
                ],
            ],
            [
                'match_keyword' => 'Kesehatan',
                'agency_name' => 'Dinas Kesehatan',
                'address' => 'Jl. Jemursari No. 197, Sidosermo, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60239',
                'phone' => '(031) 8439473',
                'email' => 'dinkes@surabaya.go.id',
                'website' => 'https://dinkes.surabaya.go.id',
                'logo' => 'images/logos/dinkes.png',
                'signee_name' => 'Nanik Sukristina, S.KM., M.Kes.',
                'signee_nip' => '19710920 199503 2 003',
                'signee_position' => 'Kepala Dinas Kesehatan',
                'admin_email' => 'admin.dinkes@surabaya.go.id',
                'admin_name' => 'Admin Dinkes Surabaya',
                'units' => [
                    ['name' => 'Bidang Pelayanan Kesehatan & Rujukan', 'quota' => 10, 'description' => 'Pengawasan operasional Puskesmas, akreditasi fasilitas kesehatan, dan sistem rujukan terpadu.'],
                    ['name' => 'Bidang Pencegahan & Pengendalian Penyakit (P2P)', 'quota' => 8, 'description' => 'Surveilans epidemiologi, imunisasi massal, dan pengendalian penyakit menular/tidak menular.'],
                    ['name' => 'Bidang Sumber Daya Kesehatan & Kefarmasian', 'quota' => 8, 'description' => 'Manajemen logistik obat-obatan, pengawasan izin sarana kefarmasian dan tenaga medis.'],
                ],
            ],
            [
                'match_keyword' => 'Sumber Daya Air',
                'agency_name' => 'Dinas Sumber Daya Air dan Bina Marga',
                'address' => 'Jl. Jimerto No. 6-8, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5343000',
                'email' => 'dsdabm@surabaya.go.id',
                'website' => 'https://dsdabm.surabaya.go.id',
                'logo' => 'images/logos/dsdabm.png',
                'signee_name' => 'Sjamsul Hariadi, S.T., M.T.',
                'signee_nip' => '19730412 199803 1 005',
                'signee_position' => 'Kepala Dinas Sumber Daya Air dan Bina Marga',
                'admin_email' => 'admin.dsdabm@surabaya.go.id',
                'admin_name' => 'Admin DSDABM Surabaya',
                'units' => [
                    ['name' => 'Bidang Drainase dan Pengendalian Banjir', 'quota' => 10, 'description' => 'Operasional rumah pompa, normalisasi saluran primer/sekunder, dan mitigasi genangan air.'],
                    ['name' => 'Bidang Jalan dan Jembatan', 'quota' => 10, 'description' => 'Pemeliharaan berkala infrastruktur jalan kota, pembangunan jembatan, dan rekayasa sipil kebinamargaan.'],
                ],
            ],
            [
                'match_keyword' => 'Perumahan Rakyat',
                'agency_name' => 'Dinas Perumahan Rakyat dan Kawasan Permukiman serta Pertanahan',
                'address' => 'Jl. Taman Surya No. 1, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'dprkpp@surabaya.go.id',
                'website' => 'https://dprkpp.surabaya.go.id',
                'logo' => 'images/logos/dprkpp.png',
                'signee_name' => 'Ir. Lilik Arijanto, M.T.',
                'signee_nip' => '19690810 199703 1 004',
                'signee_position' => 'Kepala Dinas Perumahan Rakyat dan Kawasan Permukiman serta Pertanahan',
                'admin_email' => 'admin.dprkpp@surabaya.go.id',
                'admin_name' => 'Admin DPRKPP Surabaya',
                'units' => [
                    ['name' => 'Bidang Perumahan dan Kawasan Permukiman', 'quota' => 8, 'description' => 'Pengelolaan rumah susun sewa (Rusunawa), bedah rumah tidak layak huni (Rutilahu), dan permukiman.'],
                    ['name' => 'Bidang Pertanahan dan Penataan Ruang', 'quota' => 8, 'description' => 'Pengadaan tanah fasilitas umum, sertifikasi aset tanah Pemkot, dan tata ruang kawasan perkotaan.'],
                ],
            ],
            [
                'match_keyword' => 'Lingkungan Hidup',
                'agency_name' => 'Dinas Lingkungan Hidup',
                'address' => 'Jl. Menur No. 31A, Manyar Sabrangan, Kec. Mulyorejo, Kota Surabaya, Jawa Timur 60116',
                'phone' => '(031) 5994016',
                'email' => 'dlh@surabaya.go.id',
                'website' => 'https://dlh.surabaya.go.id',
                'logo' => 'images/logos/dlh.png',
                'signee_name' => 'Dedik Irianto, S.Sos., M.M.',
                'signee_nip' => '19701104 199603 1 002',
                'signee_position' => 'Kepala Dinas Lingkungan Hidup',
                'admin_email' => 'admin.dlh@surabaya.go.id',
                'admin_name' => 'Admin DLH Surabaya',
                'units' => [
                    ['name' => 'Bidang Kebersihan dan Pengelolaan Ruang Terbuka Hijau', 'quota' => 10, 'description' => 'Pengelolaan taman kota, bank sampah induk, dan logistik kebersihan lingkungan perkotaan.'],
                    ['name' => 'Bidang Pengendalian Pencemaran dan Konservasi', 'quota' => 8, 'description' => 'Pemantauan baku mutu kualitas udara/air, pengawasan limbah B3, dan audit lingkungan industri.'],
                ],
            ],
            [
                'match_keyword' => 'Perhubungan',
                'agency_name' => 'Dinas Perhubungan',
                'address' => 'Jl. Dukuh Menanggal No. 1, Dukuh Menanggal, Kec. Gayungan, Kota Surabaya, Jawa Timur 60234',
                'phone' => '(031) 8290351',
                'email' => 'dishub@surabaya.go.id',
                'website' => 'https://dishub.surabaya.go.id',
                'logo' => 'images/logos/dishub.png',
                'signee_name' => 'Tundjung Iswandaru, S.T., M.M.',
                'signee_nip' => '19720417 199803 1 006',
                'signee_position' => 'Kepala Dinas Perhubungan',
                'admin_email' => 'admin.dishub@surabaya.go.id',
                'admin_name' => 'Admin Dishub Surabaya',
                'units' => [
                    ['name' => 'Bidang Lalu Lintas dan Manajemen Angkutan Jalan', 'quota' => 10, 'description' => 'Operasional Surabaya Intelligent Transportation System (SITS), rekayasa arus lalu lintas, dan WiraWiri/Suroboyo Bus.'],
                    ['name' => 'Bidang Sarana dan Prasarana Transportasi', 'quota' => 8, 'description' => 'Pemeliharaan rambu, marka jalan, traffic light cerdas, dan fasilitas terminal antar moda.'],
                ],
            ],
            [
                'match_keyword' => 'Sosial',
                'agency_name' => 'Dinas Sosial',
                'address' => 'Jl. Arief Rahman Hakim No. 150, Keputih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60111',
                'phone' => '(031) 59174514',
                'email' => 'dinsos@surabaya.go.id',
                'website' => 'https://dinsos.surabaya.go.id',
                'logo' => 'images/logos/dinsos.png',
                'signee_name' => 'Anna Fajriatin, A.P., M.M.',
                'signee_nip' => '19750523 199311 2 001',
                'signee_position' => 'Kepala Dinas Sosial',
                'admin_email' => 'admin.dinsos@surabaya.go.id',
                'admin_name' => 'Admin Dinsos Surabaya',
                'units' => [
                    ['name' => 'Bidang Perlindungan dan Jaminan Sosial', 'quota' => 8, 'description' => 'Pengelolaan data kemiskinan ekstrem, penyaluran bantuan sosial non-tunai, dan tanggap darurat bencana sosial.'],
                    ['name' => 'Bidang Pemberdayaan dan Rehabilitasi Sosial', 'quota' => 8, 'description' => 'Rehabilitasi sosial PMKS, pembinaan panti sosial, dan pemberdayaan ekonomi keluarga pra-sejahtera.'],
                ],
            ],
            [
                'match_keyword' => 'Tenaga Kerja',
                'agency_name' => 'Dinas Perindustrian dan Tenaga Kerja',
                'address' => 'Jl. Jemursari Timur II No. 2, Jemur Wonosari, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60237',
                'phone' => '(031) 8473852',
                'email' => 'disnaker@surabaya.go.id',
                'website' => 'https://disnaker.surabaya.go.id',
                'logo' => 'images/logos/disperinaker.png',
                'signee_name' => 'Achmad Zaini, S.Sos., M.Si.',
                'signee_nip' => '19680312 199103 1 008',
                'signee_position' => 'Kepala Dinas Tenaga Kerja',
                'admin_email' => 'admin.disnaker@surabaya.go.id',
                'admin_name' => 'Admin Disnaker Surabaya',
                'units' => [
                    ['name' => 'Bidang Penempatan Tenaga Kerja & Transmigrasi', 'quota' => 8, 'description' => 'Fasilitasi bursa kerja digital (Job Fair), kartu pencari kerja (AK-1), dan kemitraan industri.'],
                    ['name' => 'Bidang Hubungan Industrial & Pelatihan Kerja', 'quota' => 8, 'description' => 'Mediasi perselisihan ketenagakerjaan, uji kompetensi vokasi balai latihan kerja (BLK), dan UMK.'],
                ],
            ],
            [
                'match_keyword' => 'Pemberdayaan Perempuan',
                'agency_name' => 'Dinas Pemberdayaan Perempuan dan Perlindungan Anak serta Pengendalian Penduduk dan Keluarga Berencana',
                'address' => 'Jl. Ngagel Timur No. 34, Pucang Sewu, Kec. Gubeng, Kota Surabaya, Jawa Timur 60283',
                'phone' => '(031) 5035222',
                'email' => 'dp3ap2kb@surabaya.go.id',
                'website' => 'https://dp3ap2kb.surabaya.go.id',
                'logo' => 'images/logos/surabaya.png',
                'signee_name' => 'Ida Widayati, S.H., M.Si.',
                'signee_nip' => '19710814 199603 2 002',
                'signee_position' => 'Kepala Dinas DP3A-P2KB',
                'admin_email' => 'admin.dp3ap2kb@surabaya.go.id',
                'admin_name' => 'Admin DP3A-P2KB Surabaya',
                'units' => [
                    ['name' => 'Bidang Pemberdayaan Perempuan & Perlindungan Anak', 'quota' => 8, 'description' => 'Advokasi hukum korban kekerasan, pemenuhan hak anak, dan sosialisasi Surabaya Kota Layak Anak.'],
                    ['name' => 'Bidang Pengendalian Penduduk & Keluarga Berencana', 'quota' => 8, 'description' => 'Program percepatan penurunan stunting, pembinaan kader posyandu, dan ketahanan keluarga.'],
                ],
            ],
            [
                'match_keyword' => 'Ketahanan Pangan',
                'agency_name' => 'Dinas Ketahanan Pangan dan Pertanian',
                'address' => 'Jl. Pagesangan II No. 56, Pagesangan, Kec. Jambangan, Kota Surabaya, Jawa Timur 60233',
                'phone' => '(031) 8282329',
                'email' => 'dkpp@surabaya.go.id',
                'website' => 'https://dkpp.surabaya.go.id',
                'logo' => 'images/logos/dkpp.png',
                'signee_name' => 'Antiek Sugiharti, S.Sos., M.Si.',
                'signee_nip' => '19670514 199303 2 005',
                'signee_position' => 'Kepala Dinas Ketahanan Pangan dan Pertanian',
                'admin_email' => 'admin.dkpp@surabaya.go.id',
                'admin_name' => 'Admin DKPP Surabaya',
                'units' => [
                    ['name' => 'Bidang Ketahanan Pangan & Distribusi', 'quota' => 8, 'description' => 'Pengawasan keamanan pangan segar, stabilitas harga pasokan pokok, dan cadangan pangan perkotaan.'],
                    ['name' => 'Bidang Pertanian, Peternakan & Perikanan Perkotaan', 'quota' => 8, 'description' => 'Pengembangan urban farming hidroponik, kesehatan hewan ternak, dan budidaya perikanan air tawar.'],
                ],
            ],
            [
                'match_keyword' => 'Koperasi',
                'agency_name' => 'Dinas Koperasi Usaha Kecil dan Menengah dan Perdagangan',
                'address' => 'Jl. Raya Menur No. 31C, Manyar Sabrangan, Kec. Mulyorejo, Kota Surabaya, Jawa Timur 60116',
                'phone' => '(031) 5994017',
                'email' => 'dinkopumdag@surabaya.go.id',
                'website' => 'https://dinkopumdag.surabaya.go.id',
                'logo' => 'images/logos/dinkopumdag.png',
                'signee_name' => 'Dewi Soeriyawati, S.E., M.Si.',
                'signee_nip' => '19730219 199803 2 003',
                'signee_position' => 'Kepala Dinas Koperasi, UKM dan Perdagangan',
                'admin_email' => 'admin.dinkopumdag@surabaya.go.id',
                'admin_name' => 'Admin Dinkopumdag Surabaya',
                'units' => [
                    ['name' => 'Bidang Pemberdayaan Koperasi dan Usaha Mikro', 'quota' => 10, 'description' => 'Digitalisasi UMKM Surabaya Kriya Gallery (SKG), fasilitasi NIB sertifikasi halal, dan permodalan mikro.'],
                    ['name' => 'Bidang Pengembangan Perdagangan', 'quota' => 8, 'description' => 'Operasional pasar rakyat, perlindungan konsumen, dan promosi komoditas ekspor daerah.'],
                ],
            ],
            [
                'match_keyword' => 'Kebudayaan',
                'agency_name' => 'Dinas Kebudayaan, Kepemudaan dan Olahraga serta Pariwisata',
                'address' => 'Gedung Siola Lt. 2, Jl. Tunjungan No. 1-3, Genteng, Kota Surabaya, Jawa Timur 60275',
                'phone' => '(031) 5343000',
                'email' => 'disbudporapar@surabaya.go.id',
                'website' => 'https://disbudporapar.surabaya.go.id',
                'logo' => 'images/logos/disbudporapar.png',
                'signee_name' => 'Hidayat Syah, S.STP., M.AP.',
                'signee_nip' => '19790612 199711 1 001',
                'signee_position' => 'Kepala Dinas Kebudayaan, Kepemudaan dan Olahraga serta Pariwisata',
                'admin_email' => 'admin.disbudporapar@surabaya.go.id',
                'admin_name' => 'Admin Disbudporapar Surabaya',
                'units' => [
                    ['name' => 'Bidang Kebudayaan dan Cagar Budaya', 'quota' => 8, 'description' => 'Preservasi bangunan cagar budaya bersejarah, kesenian tradisional Surabaya, dan museum daerah.'],
                    ['name' => 'Bidang Kepemudaan, Olahraga dan Pariwisata', 'quota' => 10, 'description' => 'Pemberdayaan event pemuda kreatif, pembinaan atlet daerah (KORMI/KONI), dan destinasi wisata Kota Pahlawan.'],
                ],
            ],
            [
                'match_keyword' => 'Pemadam Kebakaran',
                'agency_name' => 'Dinas Pemadam Kebakaran dan Penyelamatan',
                'address' => 'Jl. Pasar Turi No. 21, Krembangan Selatan, Kec. Krembangan, Kota Surabaya, Jawa Timur 60175',
                'phone' => '(031) 3550113',
                'email' => 'dpkp@surabaya.go.id',
                'website' => 'https://dpkp.surabaya.go.id',
                'logo' => 'images/logos/dpkp.png',
                'signee_name' => 'Laksita Rini Sevriani, S.E., M.Si.',
                'signee_nip' => '19760721 200003 2 002',
                'signee_position' => 'Kepala Dinas Pemadam Kebakaran dan Penyelamatan',
                'admin_email' => 'admin.dpkp@surabaya.go.id',
                'admin_name' => 'Admin DPKP Surabaya',
                'units' => [
                    ['name' => 'Bidang Operasional Penyelamatan & Pemadaman', 'quota' => 8, 'description' => 'Manajemen pos pemadam respon cepat (Response Time < 7 Menit), animal rescue, dan evakuasi korban.'],
                    ['name' => 'Bidang Pencegahan & Pemberdayaan Masyarakat', 'quota' => 6, 'description' => 'Inspeksi proteksi kebakaran gedung bertingkat, pelatihan relawan damkar, dan simulasi keselamatan publik.'],
                ],
            ],
            [
                'match_keyword' => 'Satuan Polisi Pamong Praja',
                'agency_name' => 'Satuan Polisi Pamong Praja',
                'address' => 'Jl. Jaksa Agung Suprapto No. 4, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5343000',
                'email' => 'satpolpp@surabaya.go.id',
                'website' => 'https://satpolpp.surabaya.go.id',
                'logo' => 'images/logos/satpolpp.png',
                'signee_name' => 'M. Fikser, A.P., M.M.',
                'signee_nip' => '19740520 199311 1 001',
                'signee_position' => 'Kepala Satuan Polisi Pamong Praja',
                'admin_email' => 'admin.satpolpp@surabaya.go.id',
                'admin_name' => 'Admin Satpol PP Surabaya',
                'units' => [
                    ['name' => 'Bidang Penegakan Peraturan Daerah & Disiplin Aparatur', 'quota' => 8, 'description' => 'Operasi yustisi kepatuhan Perda, penindakan pelanggaran izin usaha, dan pembinaan ketertiban.'],
                    ['name' => 'Bidang Ketertiban Umum dan Ketenteraman Masyarakat', 'quota' => 10, 'description' => 'Patroli ketenteraman wilayah, penataan ruang publik dan pedestrian, serta pengamanan objek vital daerah.'],
                ],
            ],

            // =====================================================================
            // B. BADAN DAERAH KOTA SURABAYA
            // =====================================================================
            [
                'match_keyword' => 'Bappedalitbang',
                'agency_name' => 'Badan Perencanaan Pembangunan Daerah, Penelitian dan Pengembangan',
                'address' => 'Jl. Pacar No. 8, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5343000',
                'email' => 'bappedalitbang@surabaya.go.id',
                'website' => 'https://bappedalitbang.surabaya.go.id',
                'logo' => 'images/logos/bappedalitbang.png',
                'signee_name' => 'Irfan Widyanto, A.P., M.Si.',
                'signee_nip' => '19740618 199311 1 002',
                'signee_position' => 'Kepala Bappedalitbang Kota Surabaya',
                'admin_email' => 'admin.bappedalitbang@surabaya.go.id',
                'admin_name' => 'Admin Bappedalitbang Surabaya',
                'units' => [
                    ['name' => 'Bidang Perencanaan Ekonomi dan Pembangunan Daerah', 'quota' => 10, 'description' => 'Penyusunan RPJMD, evaluasi RKPD tahunan, dan analisis makro ekonomi perkotaan.'],
                    ['name' => 'Bidang Penelitian, Pengembangan & Inovasi Teknologi', 'quota' => 10, 'description' => 'Kajian kelayakan kebijakan publik, inkubasi riset akademisi, dan inovasi pelayanan smart city.'],
                ],
            ],
            [
                'match_keyword' => 'Pengelolaan Keuangan',
                'agency_name' => 'Badan Pengelolaan Keuangan dan Aset Daerah',
                'address' => 'Jl. Jimerto No. 25-27 Lt. 3, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'bpkad@surabaya.go.id',
                'website' => 'https://bpkad.surabaya.go.id',
                'logo' => 'images/logos/bpkad.png',
                'signee_name' => 'Syamsul Hariadi, S.T., M.Si.',
                'signee_nip' => '19710314 199603 1 003',
                'signee_position' => 'Kepala BPKAD Kota Surabaya',
                'admin_email' => 'admin.bpkad@surabaya.go.id',
                'admin_name' => 'Admin BPKAD Surabaya',
                'units' => [
                    ['name' => 'Bidang Anggaran & Perbendaharaan Keuangan', 'quota' => 8, 'description' => 'Akuntansi keuangan daerah, penyusunan APBD, dan penatausahaan kas umum daerah.'],
                    ['name' => 'Bidang Pengelolaan & Inventarisasi Aset Daerah', 'quota' => 8, 'description' => 'Pencatatan aset barang milik daerah (BMD), pemindahtanganan, dan optimalisasi aset produktif.'],
                ],
            ],
            [
                'match_keyword' => 'Pendapatan',
                'agency_name' => 'Badan Pendapatan Daerah',
                'address' => 'Jl. Jimerto No. 25-27 Lt. 1-2, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'bapenda@surabaya.go.id',
                'website' => 'https://bapenda.surabaya.go.id',
                'logo' => 'images/logos/surabaya.png',
                'signee_name' => 'Febrina Kusumawati, S.Si., M.M.',
                'signee_nip' => '19750212 199803 2 005',
                'signee_position' => 'Kepala Bapenda Kota Surabaya',
                'admin_email' => 'admin.bapenda@surabaya.go.id',
                'admin_name' => 'Admin Bapenda Surabaya',
                'units' => [
                    ['name' => 'Bidang Pajak Daerah & Retribusi', 'quota' => 10, 'description' => 'Penetapan PBB-P2, BPHTB, pajak restoran/hotel, dan digitalisasi pembayaran kanal online.'],
                    ['name' => 'Bidang Pengawasan & Teknologi Informasi Pendapatan', 'quota' => 8, 'description' => 'Uji petik kepatuhan wajib pajak, audit tapping box, dan integrasi big data pendapatan asli daerah.'],
                ],
            ],
            [
                'match_keyword' => 'Kepegawaian',
                'agency_name' => 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia',
                'address' => 'Jl. Jimerto No. 25-27 Lt. 5, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'bkpsdm@surabaya.go.id',
                'website' => 'https://bkpsdm.surabaya.go.id',
                'logo' => 'images/logos/bkpsdm.png',
                'signee_name' => 'Ira Tursilowati, S.Sos., M.M.',
                'signee_nip' => '19730815 199703 2 004',
                'signee_position' => 'Kepala BKPSDM Kota Surabaya',
                'admin_email' => 'admin.bkpsdm@surabaya.go.id',
                'admin_name' => 'Admin BKPSDM Surabaya',
                'units' => [
                    ['name' => 'Bidang Pengadaan, Mutasi dan Informasi Kepegawaian', 'quota' => 8, 'description' => 'Pengelolaan data SIM-ASN, kenaikan pangkat, formasi seleksi CPNS/PPPK, dan mutasi internal.'],
                    ['name' => 'Bidang Pengembangan Kompetensi dan Kinerja Aparatur', 'quota' => 8, 'description' => 'Pelatihan kepemimpinan, evaluasi SKP elektronik, dan asesmen talenta pegawai negeri.'],
                ],
            ],
            [
                'match_keyword' => 'Penanggulangan Bencana',
                'agency_name' => 'Badan Penanggulangan Bencana Daerah',
                'address' => 'Jl. Jemursari No. 197, Sidosermo, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60239',
                'phone' => '(031) 8411112',
                'email' => 'bpbd@surabaya.go.id',
                'website' => 'https://bpbd.surabaya.go.id',
                'logo' => 'images/logos/bpbd.png',
                'signee_name' => 'Agus Hebi Djuniantoro, S.T., M.T.',
                'signee_nip' => '19720610 199803 1 004',
                'signee_position' => 'Kepala Pelaksana BPBD Kota Surabaya',
                'admin_email' => 'admin.bpbd@surabaya.go.id',
                'admin_name' => 'Admin BPBD Surabaya',
                'units' => [
                    ['name' => 'Bidang Kesiapsiagaan, Pencegahan dan Mitigasi', 'quota' => 8, 'description' => 'Pusat Pengendalian Operasi (Pusdalops 112), peringatan dini cuaca ekstrem, dan edukasi kebencanaan.'],
                    ['name' => 'Bidang Kedaruratan, Logistik, Rehabilitasi & Rekonstruksi', 'quota' => 8, 'description' => 'Penyaluran logistik bencana, dapur umum darurat, dan rehabilitasi infrastruktur pasca musibah.'],
                ],
            ],
            [
                'match_keyword' => 'Inspektorat',
                'agency_name' => 'Inspektorat Kota Surabaya',
                'address' => 'Jl. Sedap Malam No. 1, Ketabang, Kec. Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5343000',
                'email' => 'inspektorat@surabaya.go.id',
                'website' => 'https://inspektorat.surabaya.go.id',
                'logo' => 'images/logos/inspektorat.png',
                'signee_name' => 'R. Rachmad Basari, S.H., M.Si.',
                'signee_nip' => '19681120 199303 1 003',
                'signee_position' => 'Inspektur Kota Surabaya',
                'admin_email' => 'admin.inspektorat@surabaya.go.id',
                'admin_name' => 'Admin Inspektorat Surabaya',
                'units' => [
                    ['name' => 'Inspektur Pembantu Wilayah Pemerintahan & Kesra', 'quota' => 6, 'description' => 'Pengawasan audit operasional tata kelola pemerintahan, kepatuhan hukum, dan layanan kemasyarakatan.'],
                    ['name' => 'Inspektur Pembantu Wilayah Perekonomian & Pembangunan', 'quota' => 6, 'description' => 'Audit kinerja proyek infrastruktur fisik, pengadaan barang dan jasa, serta pencegahan fraud.'],
                ],
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($agencies as $item) {
            $adminEmail = $item['admin_email'];
            $adminName = $item['admin_name'];
            $units = $item['units'] ?? [];
            $keyword = $item['match_keyword'] ?? null;

            unset($item['admin_email'], $item['admin_name'], $item['units'], $item['match_keyword']);

            $item['government_name'] = $item['government_name'] ?? 'Pemerintah Kota Surabaya';
            $item['city'] = $item['city'] ?? 'Surabaya';

            $acronymMap = [
                'Komunikasi' => 'Diskominfo',
                'Perpustakaan' => 'Dispusip',
                'Kependudukan' => 'Dispendukcapil',
                'Pendidikan' => 'Dispendik',
                'Kesehatan' => 'Dinkes',
                'Sumber Daya Air' => 'DSDABM',
                'Perumahan Rakyat' => 'DPRKPP',
                'Lingkungan Hidup' => 'DLH',
                'Perhubungan' => 'Dishub',
                'Sosial' => 'Dinsos',
                'Tenaga Kerja' => 'Disperinaker',
                'Pemberdayaan Perempuan' => 'DP3A-P2KB',
                'Ketahanan Pangan' => 'DKPP',
                'Koperasi' => 'Dinkopumdag',
                'Kebudayaan' => 'Disbudporapar',
                'Pemadam Kebakaran' => 'DPKP',
                'Satuan Polisi Pamong Praja' => 'Satpol PP',
                'Bappedalitbang' => 'Bappedalitbang',
                'Pengelolaan Keuangan' => 'BPKAD',
                'Pendapatan' => 'Bapenda',
                'Kepegawaian' => 'BKPSDM',
                'Penanggulangan Bencana' => 'BPBD',
                'Inspektorat' => 'Inspektorat',
            ];
            if ($keyword && isset($acronymMap[$keyword])) {
                $item['acronym'] = $acronymMap[$keyword];
            }

            // Sinkronisasi: Cari instansi yang sudah ada berdasarkan keyword nama atau nama persis
            $query = AgencyProfile::whereRaw('LOWER(agency_name) = ?', [strtolower($item['agency_name'])]);
            if ($keyword) {
                $query->orWhere('agency_name', 'like', '%' . $keyword . '%');
            }
            $agency = $query->first();

            if ($agency) {
                $agency->update($item);
            } else {
                $agency = AgencyProfile::create($item);
            }

            // 1. Pastikan Akun Admin Dinas Resmi Terdaftar
            $existingAdmin = User::where('agency_profile_id', $agency->id)
                ->where('role', 'admin')
                ->first();

            if (!$existingAdmin) {
                User::firstOrCreate(
                    ['email' => $adminEmail],
                    [
                        'name' => $adminName,
                        'password' => $defaultPassword,
                        'role' => 'admin',
                        'agency_profile_id' => $agency->id,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );
            } else {
                // Pertahankan akun yang ada dan pastikan statusnya aktif
                $existingAdmin->update([
                    'status' => 'active',
                    'agency_profile_id' => $agency->id,
                ]);
            }

            // 2. Pastikan Unit / Bidang Kerja Magang Tersedia
            foreach ($units as $unitData) {
                Unit::firstOrCreate(
                    [
                        'agency_profile_id' => $agency->id,
                        'name' => $unitData['name'],
                    ],
                    [
                        'description' => $unitData['description'] ?? null,
                        'quota' => $unitData['quota'] ?? 10,
                    ]
                );
            }
        }
    }
}
