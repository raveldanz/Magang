<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use App\Support\UploadLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi demo: berkas 30 MB pada kolom "Maks 2 MB" tetap terunggah lama, lalu biodata yang
 * sudah diketik (nama, NIM, kampus, semester) hilang karena PHP membuang seluruh isi form.
 */
class UploadSafetyTest extends TestCase
{
    use RefreshDatabase;

    private University $univ;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->univ = University::create(['name' => 'Universitas Unggah', 'code' => 'UU']);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
    }

    private function biodata(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Siti Demo',
            'nim' => '22081010999',
            'university_id' => (string) $this->univ->id,
            'faculty' => 'Teknik',
            'jurusan' => 'Informatika',
            'semester' => '5',
            'phone' => '081234567890',
        ], $overrides);
    }

    public function test_every_named_file_input_declares_its_size_limit(): void
    {
        $missing = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            $html = $file->getContents();
            preg_match_all('/<input\b[^>]*type="file"[^>]*>/is', $html, $inputs);
            foreach ($inputs[0] as $tag) {
                // Input tanpa name tidak ikut terkirim lewat form (mis. lampiran chat yang dikirim via fetch dengan batasnya sendiri).
                if (! preg_match('/\sname="/', $tag)) {
                    continue;
                }
                if (! preg_match('/data-max-kb="([^"]+)"/', $tag, $m) || $m[1] === '') {
                    $missing[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)), 'Kolom unggah wajib punya data-max-kb agar penjaga unggah menolak berkas sebelum dikirim.');
    }

    public function test_php_size_notation_is_converted_to_kilobytes(): void
    {
        $this->assertSame(2048, UploadLimits::toKb('2M'));
        $this->assertSame(512, UploadLimits::toKb('512K'));
        $this->assertSame(1048576, UploadLimits::toKb('1G'));
        $this->assertSame(8192, UploadLimits::toKb('8388608'));
        $this->assertSame(0, UploadLimits::toKb(''));
        $this->assertSame(0, UploadLimits::toKb('0'));
    }

    public function test_profile_page_exposes_limits_and_autosaves_biodata(): void
    {
        $html = $this->actingAs($this->student)->get(route('student.profile.edit'))->assertOk()->getContent();

        $this->assertStringContainsString('name="upload-file-max-kb" content="'.UploadLimits::phpFileMaxKb().'"', $html);
        $this->assertStringContainsString('name="upload-post-max-kb" content="'.UploadLimits::phpPostMaxKb().'"', $html);
        $this->assertStringContainsString('data-autosave="profil-'.$this->student->id.'"', $html);
        $this->assertMatchesRegularExpression('/data-max-kb="2048"[^>]*name="cv"|name="cv"[^>]*data-max-kb="2048"/s', $html);
        $this->assertStringContainsString('@restore-value="restoreValue($event.detail)"', $html, 'Pilihan kampus harus bisa dipulihkan.');
    }

    public function test_oversized_document_is_rejected_and_typed_biodata_is_kept(): void
    {
        $response = $this->actingAs($this->student)
            ->from(route('student.profile.edit'))
            ->post(route('student.profile.update'), $this->biodata([
                'cv' => UploadedFile::fake()->create('cv.pdf', 30 * 1024, 'application/pdf'),
            ]));

        $response->assertRedirect(route('student.profile.edit'));
        $response->assertSessionHasErrors(['cv']);
        $response->assertSessionHasInput('nim', '22081010999');
        $response->assertSessionHasInput('semester', '5');
        $response->assertSessionHasInput('university_id', (string) $this->univ->id);
        $this->assertNull($this->student->fresh()->studentProfile?->cv_path);
    }

    public function test_request_above_post_max_size_returns_readable_message_instead_of_error_page(): void
    {
        $response = $this->actingAs($this->student)
            ->from(route('student.profile.edit'))
            ->call('POST', route('student.profile.update'), [], [], [], [
                'CONTENT_LENGTH' => (string) ((UploadLimits::phpPostMaxKb() + 1024) * 1024),
            ]);

        $response->assertRedirect(route('student.profile.edit'));
        $response->assertSessionHas('error', fn (string $message) => str_contains($message, 'batas server')
            && str_contains($message, 'dipulihkan otomatis'));
    }

    public function test_success_flash_is_announced_so_saved_drafts_are_discarded(): void
    {
        // Halaman tujuan redirect membawa flash sukses -> forms.js menghapus draf yang baru dikirim.
        $this->actingAs($this->student)
            ->from(route('student.profile.edit'))
            ->followingRedirects()
            ->post(route('student.profile.update'), $this->biodata())
            ->assertOk()
            ->assertSee('<meta name="flash-success" content="1">', false);

        $this->assertSame('22081010999', $this->student->fresh()->studentProfile?->nim);

        // Halaman biasa (tanpa kiriman sukses) tidak boleh membawa penanda itu, agar draf tidak terhapus.
        $this->actingAs($this->student)->get(route('student.profile.edit'))
            ->assertDontSee('<meta name="flash-success" content="1">', false);
    }
}
