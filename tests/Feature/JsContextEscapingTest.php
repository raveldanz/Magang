<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

/**
 * Regresi: nilai teks bebas yang ditaruh ke string JavaScript dengan kutip manual
 * (contoh: name: '{{ old('name') }}') memutus komponen Alpine begitu nilainya memuat apostrof
 * ("Universitas Ma'arif") dan membuka celah injeksi skrip. Nilai seperti itu wajib lewat @js(...).
 */
class JsContextEscapingTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);
    }

    public function test_nama_kampus_berapostrof_tidak_memutus_form_tambah_universitas(): void
    {
        $admin = $this->superAdmin();

        // Validasi sengaja digagalkan (kode kosong) supaya old() terisi.
        $this->actingAs($admin)
            ->from(route('admin.universities.create'))
            ->post(route('admin.universities.store'), [
                'name' => "Universitas Ma'arif",
                'code' => '',
                'address' => "Jl. K.H. Hasyim Asy'ari No. 1",
            ])
            ->assertRedirect(route('admin.universities.create'))
            ->assertSessionHasErrors('code');

        $html = $this->actingAs($admin)->get(route('admin.universities.create'))->assertOk()->getContent();

        $this->assertStringContainsString("name: 'Universitas Ma".'\\'."u0027arif'", $html);
        $this->assertStringNotContainsString("name: 'Universitas Ma&#039;arif'", $html);
        $this->assertStringNotContainsString("address: 'Jl. K.H. Hasyim Asy&#039;ari", $html);
    }

    public function test_parameter_role_di_alamat_tidak_bisa_menyisipkan_skrip(): void
    {
        $payload = "x'};alert(1);//";

        $html = $this->actingAs($this->superAdmin())
            ->get(route('admin.users.index', ['role' => $payload]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('role: &#039;x&#039;};alert(1)', $html);
        $this->assertStringNotContainsString("role: 'x&#039;};alert(1)", $html);
        $this->assertStringContainsString('x\u0027};alert(1);', $html);
    }

    public function test_tidak_ada_old_atau_request_yang_dikutip_manual_di_view(): void
    {
        $pelanggaran = collect(File::allFiles(resource_path('views')))
            ->flatMap(function (SplFileInfo $file) {
                $temuan = [];
                foreach (file($file->getPathname()) as $nomor => $baris) {
                    // Kutip tunggal/backtick + {{ old( | request( | addslashes( -> string JS rakitan tangan.
                    // Atribut HTML biasa memakai kutip ganda (value="{{ old(...) }}") sehingga tidak ikut terjaring.
                    if (preg_match('/[\'`]\{\{\s*(old|request)\(/', $baris)
                        && ! preg_match('/\?\s*\'[a-z_]*\'\s*:/', $baris)) {
                        $temuan[] = $file->getRelativePathname().':'.($nomor + 1);
                    }
                }

                return $temuan;
            })
            ->all();

        $this->assertSame([], $pelanggaran, 'Gunakan @js(...) untuk menaruh nilai ke JavaScript: '.implode(', ', $pelanggaran));
    }
}
