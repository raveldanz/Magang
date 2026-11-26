<?php

namespace Tests\Feature;

use Database\Seeders\DemoSampleFilesSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Pemasangan dari GitHub harus punya semua berkas contoh yang dirujuk data demo, tanpa berkas asli siapa pun. */
class DemoSampleFilesTest extends TestCase
{
    /** @return array<int, string> berkas PDF/gambar yang disebut seeder demo */
    private function referencedFiles(): array
    {
        $paths = [];
        foreach (File::files(database_path('seeders')) as $file) {
            if ($file->getFilename() === 'DemoSampleFilesSeeder.php') {
                continue;
            }
            preg_match_all('#\'((?:projects|final_reports|documents/applications)/[\w.\-]+\.(?:pdf|jpg|png))\'#', $file->getContents(), $matches);
            array_push($paths, ...$matches[1]);
        }

        return array_values(array_unique($paths));
    }

    public function test_semua_berkas_yang_dirujuk_seeder_dibuat_dan_valid(): void
    {
        Storage::fake('public');

        $this->seed(DemoSampleFilesSeeder::class);

        $files = $this->referencedFiles();
        $this->assertGreaterThan(10, count($files));
        foreach ($files as $path) {
            Storage::disk('public')->assertExists($path);
            $content = Storage::disk('public')->get($path);
            if (str_ends_with($path, '.pdf')) {
                $this->assertStringStartsWith('%PDF-', $content, $path);
            } else {
                $info = getimagesizefromstring($content);
                $this->assertNotFalse($info, $path);
                $this->assertSame(str_ends_with($path, '.png') ? IMAGETYPE_PNG : IMAGETYPE_JPEG, $info[2], $path);
            }
        }
    }

    public function test_berkas_yang_sudah_ada_tidak_ditimpa(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('projects/meja_layanan_sirkulasi.jpg', 'milik pengguna');

        $this->seed(DemoSampleFilesSeeder::class);

        $this->assertSame('milik pengguna', Storage::disk('public')->get('projects/meja_layanan_sirkulasi.jpg'));
    }

    public function test_pdf_contoh_di_repo_adalah_berkas_buatan_yang_kecil(): void
    {
        $path = storage_path('app/public/documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf');

        $this->assertFileExists($path);
        $this->assertLessThan(60 * 1024, filesize($path));
        $this->assertStringStartsWith('%PDF-', file_get_contents($path));
    }
}
