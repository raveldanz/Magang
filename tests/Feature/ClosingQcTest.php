<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Temuan QC penutup (16 Nov 2026):
 *  - di PostgreSQL, ID bukan angka pada alamat (mis. /projects/abc) berujung galat 500,
 *  - pengajuan demo tanpa dokumen membuat halaman "periksa berkas" kosong.
 */
class ClosingQcTest extends TestCase
{
    use RefreshDatabase;

    public function test_galat_id_tidak_sah_dari_postgresql_menjadi_404(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);

        foreach (['22P02', '22003'] as $sqlState) {
            $pdo = new \PDOException('invalid input syntax for type bigint: "abc"');
            $pdo->errorInfo = [$sqlState, 7, 'invalid input'];
            $exception = new QueryException('pgsql', 'select * from "internship_projects" where "id" = ?', ['abc'], $pdo);
            (new \ReflectionProperty(\Exception::class, 'code'))->setValue($exception, $sqlState);

            $response = $handler->render(Request::create('/projects/abc', 'GET'), $exception);

            $this->assertSame(404, $response->getStatusCode(), "SQLSTATE {$sqlState} harus menjadi 404");
        }
    }

    public function test_galat_database_lain_tetap_500(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);
        $pdo = new \PDOException('relation does not exist');
        $exception = new QueryException('pgsql', 'select 1', [], $pdo);
        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($exception, '42P01');

        $this->assertSame(500, $handler->render(Request::create('/x', 'GET'), $exception)->getStatusCode());
    }

    public function test_seeder_melengkapi_dokumen_pengajuan_demo_tetapi_tidak_menyentuh_email_pribadi(): void
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Uji', 'quota' => 5]);
        $univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU']);

        $demo = User::factory()->create(['role' => 'mahasiswa', 'email' => 'demo.tanpa.dokumen@unesa.ac.id', 'university_id' => $univ->id]);
        $personal = User::factory()->create(['role' => 'mahasiswa', 'email' => 'orang.sungguhan@gmail.com', 'university_id' => $univ->id]);
        foreach ([$demo, $personal] as $user) {
            StudentProfile::create(['user_id' => $user->id, 'nim' => (string) $user->id, 'universitas' => $univ->name, 'university_id' => $univ->id, 'jurusan' => 'TI', 'phone' => '08']);
        }
        $demoApp = Application::create(['user_id' => $demo->id, 'unit_id' => $unit->id, 'status' => 'pending', 'start_date' => now()->addDays(3)->toDateString(), 'end_date' => now()->addDays(60)->toDateString()]);
        $personalApp = Application::create(['user_id' => $personal->id, 'unit_id' => $unit->id, 'status' => 'pending', 'start_date' => now()->addDays(3)->toDateString(), 'end_date' => now()->addDays(60)->toDateString()]);

        $seeder = new DemoScenarioSeeder;
        (new \ReflectionMethod($seeder, 'backfillApplicationDocuments'))->invoke($seeder);

        $this->assertSame(4, ApplicationDocument::where('application_id', $demoApp->id)->count());
        $this->assertNotNull($demoApp->fresh()->cv_path);
        $this->assertSame(0, ApplicationDocument::where('application_id', $personalApp->id)->count());
        $this->assertNull($personalApp->fresh()->cv_path);

        // Menjalankan ulang tidak menggandakan dokumen.
        (new \ReflectionMethod($seeder, 'backfillApplicationDocuments'))->invoke($seeder);
        $this->assertSame(4, ApplicationDocument::where('application_id', $demoApp->id)->count());
    }
}
