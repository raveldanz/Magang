<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SurabayaAndTopUniversitiesSeeder extends Seeder
{
    /**
     * Seeder Master Data Perguruan Tinggi (Universitas Utama Surabaya & Top Nasional).
     * Dijalankan secara idempoten (updateOrCreate/firstOrCreate) tanpa merusak relasi yang ada.
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('universities', 'id'), COALESCE((SELECT MAX(id) FROM universities), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))");
        }

        $universities = [
            // =====================================================================
            // A. KAMPUS NEGERI DI SURABAYA & SEKITARNYA
            // =====================================================================
            [
                'name' => 'Institut Teknologi Sepuluh Nopember',
                'code' => 'ITS',
                'address' => 'Kampus ITS Sukolilo, Jl. Raya ITS, Keputih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60111',
                'phone' => '(031) 5994251',
                'email' => 'humas@its.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Mochamad Ashari, M.Eng.',
                'pic_nip' => '196510121991031003',
                'pic_position' => 'Rektor Institut Teknologi Sepuluh Nopember',
                'logo' => 'images/logos/its.png',
                'admin_email' => 'admin@its.ac.id',
            ],
            [
                'name' => 'Universitas Airlangga',
                'code' => 'UNAIR',
                'address' => 'Kantor Manajemen Kampus MERR C, Mulyorejo, Kota Surabaya, Jawa Timur 60115',
                'phone' => '(031) 5914042',
                'email' => 'rektor@unair.ac.id',
                'pic_name' => 'Prof. Dr. Mohammad Nasih, SE., MT., Ak.',
                'pic_nip' => '196508061992031002',
                'pic_position' => 'Rektor Universitas Airlangga',
                'logo' => 'images/logos/unair.png',
                'admin_email' => 'admin@unair.ac.id',
            ],
            [
                'name' => 'Universitas Pembangunan Nasional "Veteran" Jawa Timur',
                'code' => 'UPN',
                'acronym' => 'UPN "Veteran" Jatim',
                'address' => 'Jl. Rungkut Madya No. 1, Gunung Anyar, Kec. Rungkut, Kota Surabaya, Jawa Timur 60294',
                'phone' => '(031) 8706369',
                'email' => 'humas@upnjatim.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Akhmad Fauzi, M.MT., IPU.',
                'pic_nip' => '196508301991031002',
                'pic_position' => 'Rektor UPN "Veteran" Jawa Timur',
                'logo' => 'images/logos/upnjatim.png',
                'admin_email' => 'admin@upnjatim.ac.id',
            ],
            [
                'name' => 'Universitas Negeri Surabaya',
                'code' => 'UNESA',
                'address' => 'Jl. Lidah Wetan, Kec. Lakarsantri, Kota Surabaya, Jawa Timur 60213',
                'phone' => '(031) 99424930',
                'email' => 'humas@unesa.ac.id',
                'pic_name' => 'Prof. Dr. Nurhasan, M.Kes.',
                'pic_nip' => '196304291990021001',
                'pic_position' => 'Rektor Universitas Negeri Surabaya',
                'logo' => 'images/logos/unesa.png',
                'admin_email' => 'admin@unesa.ac.id',
            ],
            [
                'name' => 'Politeknik Elektronika Negeri Surabaya',
                'code' => 'PENS',
                'address' => 'Jl. Raya ITS, Keputih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60111',
                'phone' => '(031) 5947280',
                'email' => 'pens@pens.ac.id',
                'pic_name' => 'Ali Ridho Barakbah, S.Kom., Ph.D.',
                'pic_nip' => '197305092000031001',
                'pic_position' => 'Direktur Politeknik Elektronika Negeri Surabaya',
                'logo' => 'images/logos/pens.png',
                'admin_email' => 'admin@pens.ac.id',
            ],
            [
                'name' => 'Politeknik Perkapalan Negeri Surabaya',
                'code' => 'PPNS',
                'address' => 'Jl. Teknik Kimia Kampus ITS, Keputih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60111',
                'phone' => '(031) 5947186',
                'email' => 'humas@ppns.ac.id',
                'pic_name' => 'Eko Julianto, M.Sc., FRINA.',
                'pic_nip' => '196907141995121001',
                'pic_position' => 'Direktur Politeknik Perkapalan Negeri Surabaya',
                'logo' => 'images/logos/ppns.png',
                'admin_email' => 'admin@ppns.ac.id',
            ],
            [
                'name' => 'UIN Sunan Ampel Surabaya',
                'code' => 'UINSA',
                'address' => 'Jl. Ahmad Yani No. 117, Jemur Wonosari, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60237',
                'phone' => '(031) 8410298',
                'email' => 'humas@uinsby.ac.id',
                'pic_name' => 'Prof. Akh. Muzakki, M.Ag., Grad.Dip.SEA., M.Phil., Ph.D.',
                'pic_nip' => '197402091998031002',
                'pic_position' => 'Rektor UIN Sunan Ampel Surabaya',
                'logo' => 'images/logos/uinsa.png',
                'admin_email' => 'admin@uinsby.ac.id',
            ],
            [
                'name' => 'Poltekkes Kemenkes Surabaya',
                'code' => 'POLTEKKES-SBY',
                'address' => 'Jl. Pucang Jajar Tengah No. 56, Kertajaya, Kec. Gubeng, Kota Surabaya, Jawa Timur 60282',
                'phone' => '(031) 5027404',
                'email' => 'direktorat@poltekkesdepkes-sby.ac.id',
                'pic_name' => 'Dr. drg. Sri Hernawati, M.Kes.',
                'pic_nip' => '196705121992032001',
                'pic_position' => 'Direktur Poltekkes Kemenkes Surabaya',
                'logo' => 'images/logos/poltekkes-sby.png',
                'admin_email' => 'admin@poltekkesdepkes-sby.ac.id',
            ],

            // =====================================================================
            // B. KAMPUS SWASTA POPULER DI SURABAYA
            // =====================================================================
            [
                'name' => 'Universitas Kristen Petra',
                'code' => 'UKP',
                'acronym' => 'UK Petra',
                'address' => 'Jl. Siwalankerto No. 121-131, Siwalankerto, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60236',
                'phone' => '(031) 2983000',
                'email' => 'info@petra.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Djwantoro Hardjito, M.Eng.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Kristen Petra',
                'logo' => 'images/logos/petra.png',
                'admin_email' => 'admin@petra.ac.id',
            ],
            [
                'name' => 'Universitas Surabaya',
                'code' => 'UBAYA',
                'address' => 'Jl. Raya Kalirungkut, Kali Rungkut, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
                'phone' => '(031) 2981000',
                'email' => 'humas@unit.ubaya.ac.id',
                'pic_name' => 'Dr. Benny Lianto, M.M.B.A.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Surabaya',
                'logo' => 'images/logos/ubaya.png',
                'admin_email' => 'admin@ubaya.ac.id',
            ],
            [
                'name' => 'Universitas Ciputra Surabaya',
                'code' => 'UC',
                'address' => 'CitraLand CBD Boulevard, Made, Kec. Sambikerep, Kota Surabaya, Jawa Timur 60219',
                'phone' => '(031) 7451699',
                'email' => 'info@ciputra.ac.id',
                'pic_name' => 'Ir. Yohannes Somawiharja, M.Sc.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Ciputra Surabaya',
                'logo' => 'images/logos/ciputra.png',
                'admin_email' => 'admin@ciputra.ac.id',
            ],
            [
                'name' => 'Universitas Katolik Widya Mandala Surabaya',
                'code' => 'UKWMS',
                'address' => 'Jl. Dinoyo No. 42-44, Keputran, Kec. Tegalsari, Kota Surabaya, Jawa Timur 60265',
                'phone' => '(031) 5678478',
                'email' => 'info@ukwms.ac.id',
                'pic_name' => 'Drs. Kuncoro Foe, G.Dip.Sc., Ph.D., Apt.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Katolik Widya Mandala Surabaya',
                'logo' => 'images/logos/ukwms.png',
                'admin_email' => 'admin@ukwms.ac.id',
            ],
            [
                'name' => 'Universitas Dinamika (STIKOM Surabaya)',
                'code' => 'UNDIKA',
                'address' => 'Jl. Raya Kedung Baruk No. 98, Kedung Baruk, Kec. Rungkut, Kota Surabaya, Jawa Timur 60298',
                'phone' => '(031) 8721731',
                'email' => 'info@dinamika.ac.id',
                'pic_name' => 'Prof. Dr. Budi Jatmiko, M.Pd.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Dinamika',
                'logo' => 'images/logos/dinamika.png',
                'admin_email' => 'admin@dinamika.ac.id',
            ],
            [
                'name' => 'Universitas Wijaya Kusuma Surabaya',
                'code' => 'UWKS',
                'address' => 'Jl. Dukuh Kupang XXV No. 54, Dukuh Kupang, Kec. Dukuhpakis, Kota Surabaya, Jawa Timur 60225',
                'phone' => '(031) 5677577',
                'email' => 'info@uwks.ac.id',
                'pic_name' => 'Prof. Dr. H. Widodo Ferradi, S.H., M.H.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Wijaya Kusuma Surabaya',
                'logo' => 'images/logos/uwks.png',
                'admin_email' => 'admin@uwks.ac.id',
            ],
            [
                'name' => 'Universitas Dr. Soetomo',
                'code' => 'UNITOMO',
                'address' => 'Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60118',
                'phone' => '(031) 5925970',
                'email' => 'info@unitomo.ac.id',
                'pic_name' => 'Dr. Siti Marwiyah, S.H., M.H.',
                'pic_nip' => '196808281993032001',
                'pic_position' => 'Rektor Universitas Dr. Soetomo',
                'logo' => 'images/logos/unitomo.png',
                'admin_email' => 'admin@unitomo.ac.id',
            ],
            [
                'name' => 'Universitas Narotama',
                'code' => 'NAROTAMA',
                'address' => 'Jl. Arief Rachman Hakim No. 51, Sukolilo, Kota Surabaya, Jawa Timur 60117',
                'phone' => '(031) 5995568',
                'email' => 'info@narotama.ac.id',
                'pic_name' => 'Dr. Arasy Alimudin, S.E., M.M.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Narotama',
                'logo' => 'images/logos/narotama.png',
                'admin_email' => 'admin@narotama.ac.id',
            ],
            [
                'name' => 'Universitas Muhammadiyah Surabaya',
                'code' => 'UMSURABAYA',
                'acronym' => 'UM Surabaya',
                'address' => 'Jl. Sutorejo No. 59, Dukuh Sutorejo, Kec. Mulyorejo, Kota Surabaya, Jawa Timur 60113',
                'phone' => '(031) 3811966',
                'email' => 'rektorat@um-surabaya.ac.id',
                'pic_name' => 'Dr. dr. Sukadiono, M.M.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Muhammadiyah Surabaya',
                'logo' => 'images/logos/um-surabaya.png',
                'admin_email' => 'admin@um-surabaya.ac.id',
            ],
            [
                'name' => 'Universitas Nahdlatul Ulama Surabaya',
                'code' => 'UNUSA',
                'address' => 'Jl. Jemursari No. 57, Jemur Wonosari, Kec. Wonocolo, Kota Surabaya, Jawa Timur 60237',
                'phone' => '(031) 8479070',
                'email' => 'info@unusa.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Achmad Jazidie, M.Eng.',
                'pic_nip' => '195902191986011001',
                'pic_position' => 'Rektor Universitas Nahdlatul Ulama Surabaya',
                'logo' => 'images/logos/unusa.png',
                'admin_email' => 'admin@unusa.ac.id',
            ],
            [
                'name' => 'Universitas PGRI Adi Buana Surabaya',
                'code' => 'UNIPA',
                'address' => 'Jl. Dukuh Menanggal XII, Dukuh Menanggal, Kec. Gayungan, Kota Surabaya, Jawa Timur 60234',
                'phone' => '(031) 8281181',
                'email' => 'humas@unipasby.ac.id',
                'pic_name' => 'Dr. Hartono, M.Si.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas PGRI Adi Buana Surabaya',
                'logo' => 'images/logos/unipasby.png',
                'admin_email' => 'admin@unipasby.ac.id',
            ],
            [
                'name' => 'Universitas 17 Agustus 1945 Surabaya',
                'code' => 'UNTAG',
                'address' => 'Jl. Semolowaru No. 45, Menur Pumpungan, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60118',
                'phone' => '(031) 5931800',
                'email' => 'humas@untag-sby.ac.id',
                'pic_name' => 'Prof. Dr. Mulyanto Nugroho, M.M., CMA., CPA.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas 17 Agustus 1945 Surabaya',
                'logo' => 'images/logos/untag-sby.png',
                'admin_email' => 'admin@untag-sby.ac.id',
            ],
            [
                'name' => 'Universitas Bhayangkara Surabaya',
                'code' => 'UBHARA',
                'address' => 'Jl. Ahmad Yani No. 114, Ketintang, Kec. Gayungan, Kota Surabaya, Jawa Timur 60231',
                'phone' => '(031) 8285602',
                'email' => 'info@ubhara.ac.id',
                'pic_name' => 'Drs. Edy Prawoto, S.H., M.Hum.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Bhayangkara Surabaya',
                'logo' => 'images/logos/ubhara.png',
                'admin_email' => 'admin@ubhara.ac.id',
            ],
            [
                'name' => 'Universitas Hang Tuah',
                'code' => 'UHT',
                'address' => 'Jl. Arief Rahman Hakim No. 150, Keputih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60111',
                'phone' => '(031) 5945864',
                'email' => 'info@hangtuah.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Supartono, M.M., CIQaR.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Hang Tuah',
                'logo' => 'images/logos/hangtuah.png',
                'admin_email' => 'admin@hangtuah.ac.id',
            ],
            [
                'name' => 'Universitas Pelita Harapan Kampus Surabaya',
                'code' => 'UPH-SBY',
                'address' => 'City of Tomorrow Mall, Jl. Ahmad Yani No. 288, Dukuh Menanggal, Kec. Gayungan, Kota Surabaya, Jawa Timur 60234',
                'phone' => '(031) 58251007',
                'email' => 'surabaya.admissions@uph.edu',
                'pic_name' => 'Dr. (Hon.) Jonathan L. Parapak, M.Eng.Sc.',
                'pic_nip' => '-',
                'pic_position' => 'Rektor Universitas Pelita Harapan',
                'logo' => 'images/logos/uph.png',
                'admin_email' => 'admin@uph-sby.ac.id',
            ],

            // =====================================================================
            // C. KAMPUS TOP NASIONAL (LUAR KOTA SURABAYA)
            // =====================================================================
            [
                'name' => 'Universitas Brawijaya',
                'code' => 'UB',
                'address' => 'Jl. Veteran, Ketawanggede, Kec. Lowokwaru, Kota Malang, Jawa Timur 65145',
                'phone' => '(0341) 551611',
                'email' => 'humas@ub.ac.id',
                'pic_name' => 'Prof. Widodo, S.Si., M.Si., Ph.D.Med.Sc.',
                'pic_nip' => '197308111999031002',
                'pic_position' => 'Rektor Universitas Brawijaya',
                'logo' => 'images/logos/ub.png',
                'admin_email' => 'admin@ub.ac.id',
            ],
            [
                'name' => 'Universitas Negeri Malang',
                'code' => 'UM',
                'address' => 'Jl. Semarang No. 5, Sumbersari, Kec. Lowokwaru, Kota Malang, Jawa Timur 65145',
                'phone' => '(0341) 551312',
                'email' => 'humas@um.ac.id',
                'pic_name' => 'Prof. Dr. Hariyono, M.Pd.',
                'pic_nip' => '196312281989021001',
                'pic_position' => 'Rektor Universitas Negeri Malang',
                'logo' => 'images/logos/um.png',
                'admin_email' => 'admin@um.ac.id',
            ],
            [
                'name' => 'Universitas Jember',
                'code' => 'UNEJ',
                'address' => 'Jl. Kalimantan Tegalboto No. 37, Krajan Timur, Sumbersari, Kec. Sumbersari, Kabupaten Jember, Jawa Timur 68121',
                'phone' => '(0331) 330224',
                'email' => 'humas@unej.ac.id',
                'pic_name' => 'Dr. Ir. Iwan Taruna, M.Eng., IPM.',
                'pic_nip' => '196910051994021001',
                'pic_position' => 'Rektor Universitas Jember',
                'logo' => 'images/logos/unej.png',
                'admin_email' => 'admin@unej.ac.id',
            ],
            [
                'name' => 'Universitas Gadjah Mada',
                'code' => 'UGM',
                'address' => 'Bulaksumur, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281',
                'phone' => '(0274) 6492599',
                'email' => 'humas@ugm.ac.id',
                'pic_name' => 'Prof. dr. Ova Emilia, M.Med.Ed., Sp.OG(K)., Ph.D.',
                'pic_nip' => '196402191990032001',
                'pic_position' => 'Rektor Universitas Gadjah Mada',
                'logo' => 'images/logos/ugm.png',
                'admin_email' => 'admin@ugm.ac.id',
            ],
            [
                'name' => 'Institut Teknologi Bandung',
                'code' => 'ITB',
                'address' => 'Jl. Ganesa No. 10, Lb. Siliwangi, Kecamatan Coblong, Kota Bandung, Jawa Barat 40132',
                'phone' => '(022) 2500935',
                'email' => 'humas@itb.ac.id',
                'pic_name' => 'Prof. Reini Wirahadikusumah, Ph.D.',
                'pic_nip' => '196810251999032001',
                'pic_position' => 'Rektor Institut Teknologi Bandung',
                'logo' => 'images/logos/itb.png',
                'admin_email' => 'admin@itb.ac.id',
            ],
            [
                'name' => 'Universitas Indonesia',
                'code' => 'UI',
                'address' => 'Jl. Margonda Raya, Pondok Cina, Kecamatan Beji, Kota Depok, Jawa Barat 16424',
                'phone' => '(021) 7867222',
                'email' => 'humas-ui@ui.ac.id',
                'pic_name' => 'Prof. Ari Kuncoro, S.E., M.A., Ph.D.',
                'pic_nip' => '196201281988031001',
                'pic_position' => 'Rektor Universitas Indonesia',
                'logo' => 'images/logos/ui.png',
                'admin_email' => 'admin@ui.ac.id',
            ],
            [
                'name' => 'Universitas Diponegoro',
                'code' => 'UNDIP',
                'address' => 'Jl. Prof. Sudarto No. 13, Tembalang, Kec. Tembalang, Kota Semarang, Jawa Tengah 50275',
                'phone' => '(024) 7460012',
                'email' => 'humas@live.undip.ac.id',
                'pic_name' => 'Prof. Dr. Suharnomo, S.E., M.Si.',
                'pic_nip' => '197007221998021001',
                'pic_position' => 'Rektor Universitas Diponegoro',
                'logo' => 'images/logos/undip.png',
                'admin_email' => 'admin@undip.ac.id',
            ],
            [
                'name' => 'Universitas Sebelas Maret',
                'code' => 'UNS',
                'address' => 'Jl. Ir. Sutami No. 36A, Kentingan, Kec. Jebres, Kota Surakarta, Jawa Tengah 57126',
                'phone' => '(0271) 646994',
                'email' => 'humas@mail.uns.ac.id',
                'pic_name' => 'Prof. Dr. Jamal Wiwoho, S.H., M.Hum.',
                'pic_nip' => '196111081987021001',
                'pic_position' => 'Rektor Universitas Sebelas Maret',
                'logo' => 'images/logos/uns.png',
                'admin_email' => 'admin@uns.ac.id',
            ],
            [
                'name' => 'Universitas Terbuka',
                'code' => 'UT',
                'address' => 'Jl. Kampus Deles No. 1, Gebang Putih, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60117',
                'phone' => '(031) 5961152',
                'email' => 'ut-surabaya@ecampus.ut.ac.id',
                'pic_name' => 'Prof. Ojat Darojat, M.Bus., Ph.D.',
                'pic_nip' => '196610261991031001',
                'pic_position' => 'Rektor Universitas Terbuka',
                'logo' => 'images/logos/ut.png',
                'admin_email' => 'admin@ut.ac.id',
            ],
            [
                'name' => 'Universitas Pembangunan Nasional "Veteran" Jakarta',
                'code' => 'UPNVJ',
                'acronym' => 'UPNVJ',
                'address' => 'Jl. RS. Fatmawati Raya, Pondok Labu, Kec. Cilandak, Kota Jakarta Selatan, DKI Jakarta 12450',
                'phone' => '(021) 7656971',
                'email' => 'humas@upnvj.ac.id',
                'pic_name' => 'Prof. Dr. Anter Venus, MA.Comm.',
                'pic_nip' => '196806021993031002',
                'pic_position' => 'Rektor Universitas Pembangunan Nasional "Veteran" Jakarta',
                'logo' => 'images/logos/upnvj.png',
                'admin_email' => 'admin@upnvj.ac.id',
            ],

            // =====================================================================
            // D. OPSI FLEKSIBILITAS "LAINNYA" (KAMPUS LUAR KOTA / MANDIRI)
            // =====================================================================
            [
                'name' => 'Perguruan Tinggi Lainnya (Luar Kota)',
                'code' => 'LAINNYA',
                'acronym' => 'Lainnya',
                'address' => 'Di luar wilayah Surabaya / Kampus Terbuka Lainnya',
                'phone' => '(031) 5312144',
                'email' => 'mitra.kampus@surabaya.go.id',
                'pic_name' => 'Koordinator Kerja Sama Perguruan Tinggi',
                'pic_nip' => '-',
                'pic_position' => 'Pengelola Kemitraan Kampus Luar Wilayah',
                'logo' => 'images/default-university.svg',
                'admin_email' => 'admin@kampus-lainnya.ac.id',
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($universities as $item) {
            $adminEmail = $item['admin_email'];
            unset($item['admin_email']);

            $item['evaluation_scheme'] = $item['evaluation_scheme'] ?? 'dual_evaluation';
            $item['weight_mentor'] = $item['weight_mentor'] ?? 50;
            $item['weight_lecturer'] = $item['weight_lecturer'] ?? 50;
            $item['require_dpl'] = $item['require_dpl'] ?? true;
            $item['is_verified'] = true;
            $item['acronym'] = $item['acronym'] ?? $item['code'];
            $item['status'] = 'active';

            // Cari kampus berdasarkan code ATAU nama agar tidak memicu duplikasi ID
            $univ = University::where('code', $item['code'])
                ->orWhereRaw('LOWER(name) = ?', [strtolower($item['name'])])
                ->first();

            if ($univ) {
                $univ->update($item);
            } else {
                $univ = University::create($item);
            }

            // Pastikan akun Admin Universitas terdaftar dengan status aktif & email terverifikasi
            $existingAdmin = User::where('university_id', $univ->id)
                ->where('role', 'universitas')
                ->first();

            if (! $existingAdmin) {
                User::firstOrCreate(
                    ['email' => $adminEmail],
                    [
                        'name' => 'Admin '.$univ->name,
                        'password' => $defaultPassword,
                        'role' => 'universitas',
                        'status' => 'active',
                        'email_verified_at' => now(),
                        'university_id' => $univ->id,
                        'university' => $univ->name,
                    ]
                );
            } else {
                // Pastikan admin yang sudah ada tetap aktif & terverifikasi
                $existingAdmin->update([
                    'status' => 'active',
                    'university_id' => $univ->id,
                    'university' => $univ->name,
                ]);
            }
        }
    }
}
