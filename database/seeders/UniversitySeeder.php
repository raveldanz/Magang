<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            [
                'name' => 'Universitas Dr. Soetomo',
                'code' => 'UNITOMO',
                'address' => 'Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118',
                'phone' => '(031) 5925970',
                'email' => 'info@unitomo.ac.id',
                'pic_name' => 'Dr. Siti Marwiyah, S.H., M.H.',
                'pic_nip' => '196808281993032001',
                'pic_position' => 'Rektor Universitas Dr. Soetomo',
                'logo' => 'images/logos/unitomo.png',
            ],
            [
                'name' => 'Universitas Pembangunan Nasional Veteran Jawa Timur',
                'code' => 'UPNVJT',
                'address' => 'Jl. Rungkut Madya No. 1, Gunung Anyar, Kec. Rungkut, Surabaya, Jawa Timur 60294',
                'phone' => '(031) 8706369',
                'email' => 'humas@upnjatim.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Akhmad Fauzi, M.MT., IPU.',
                'pic_nip' => '196508301991031002',
                'pic_position' => 'Rektor UPN Veteran Jawa Timur',
                'logo' => 'images/logos/upnjatim.png',
            ],
            [
                'name' => 'UPN Veteran Jawa Timur',
                'code' => 'UPN',
                'address' => 'Jl. Rungkut Madya No. 1, Gunung Anyar, Kec. Rungkut, Surabaya, Jawa Timur 60294',
                'phone' => '(031) 8706369',
                'email' => 'humas@upnjatim.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Akhmad Fauzi, M.MT., IPU.',
                'pic_nip' => '196508301991031002',
                'pic_position' => 'Rektor UPN Veteran Jawa Timur',
                'logo' => 'images/logos/upnjatim.png',
            ],
            [
                'name' => 'Universitas Negeri Surabaya',
                'code' => 'UNESA',
                'address' => 'Jl. Lidah Wetan, Kec. Lakarsantri, Surabaya, Jawa Timur 60213',
                'phone' => '(031) 99421834',
                'email' => 'humas@unesa.ac.id',
                'pic_name' => 'Prof. Dr. Nurhasan, M.Kes.',
                'pic_nip' => '196304291990021001',
                'pic_position' => 'Rektor Universitas Negeri Surabaya',
                'logo' => 'images/logos/unesa.png',
            ],
            [
                'name' => 'Institut Teknologi Sepuluh Nopember',
                'code' => 'ITS',
                'address' => 'Kampus ITS Sukolilo, Jl. Raya ITS, Keputih, Kec. Sukolilo, Surabaya, Jawa Timur 60111',
                'phone' => '(031) 5994251',
                'email' => 'humas@its.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Mochamad Ashari, M.Eng.',
                'pic_nip' => '196510121991031003',
                'pic_position' => 'Direktur Kemitraan & Program Magang',
                'logo' => 'images/logos/its.png',
            ],
            [
                'name' => 'Universitas Airlangga',
                'code' => 'UNAIR',
                'address' => 'Kantor Manajemen Kampus MERR C, Mulyorejo, Surabaya, Jawa Timur 60115',
                'phone' => '(031) 5914042',
                'email' => 'rektor@unair.ac.id',
                'pic_name' => 'Prof. Dr. Mohammad Nasih, SE., MT., Ak.',
                'pic_nip' => '196508061992031002',
                'pic_position' => 'Rektor Universitas Airlangga',
                'logo' => 'images/logos/unair.png',
            ],
        ];

        foreach ($universities as $univData) {
            University::updateOrCreate(
                ['code' => $univData['code']],
                $univData
            );
        }
    }
}
