<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom custom_category pada tabel internship_projects
        Schema::table('internship_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('internship_projects', 'custom_category')) {
                $table->string('custom_category', 150)->nullable()->after('category');
            }
        });

        // 2. Buat tabel pivot relasi multi-anggota tim proyek inovasi
        if (! Schema::hasTable('internship_project_members')) {
            Schema::create('internship_project_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internship_project_id')->constrained('internship_projects')->onDelete('cascade');
                $table->foreignId('placement_id')->constrained('placements')->onDelete('cascade');
                $table->string('role_in_project', 150)->default('Anggota Tim');
                $table->boolean('is_leader')->default(false);
                $table->timestamps();

                $table->unique(['internship_project_id', 'placement_id']);
                $table->index('internship_project_id');
                $table->index('placement_id');
            });
        }

        // 3. Backfill data proyek lama agar uploader otomatis terdaftar sebagai leader di tabel pivot
        $existingProjects = DB::table('internship_projects')->select('id', 'placement_id', 'created_at', 'updated_at')->get();
        foreach ($existingProjects as $proj) {
            if ($proj->placement_id) {
                $exists = DB::table('internship_project_members')
                    ->where('internship_project_id', $proj->id)
                    ->where('placement_id', $proj->placement_id)
                    ->exists();

                if (! $exists) {
                    DB::table('internship_project_members')->insert([
                        'internship_project_id' => $proj->id,
                        'placement_id' => $proj->placement_id,
                        'role_in_project' => 'Koordinator Proyek',
                        'is_leader' => true,
                        'created_at' => $proj->created_at ?? now(),
                        'updated_at' => $proj->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_project_members');

        Schema::table('internship_projects', function (Blueprint $table) {
            if (Schema::hasColumn('internship_projects', 'custom_category')) {
                $table->dropColumn('custom_category');
            }
        });
    }
};

