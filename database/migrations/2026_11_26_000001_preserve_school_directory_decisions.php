<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excluded_school_codes', function (Blueprint $table) {
            $table->string('code', 8)->primary();
        });
        $corrections = json_decode(file_get_contents(database_path('data/school_directory_corrections.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($corrections as $code => $row) {
            $school = DB::table('universities')->where('code', (string) $code)->where('institution_type', 'sekolah')->whereNotNull('directory_synced_at');
            (clone $school)->where('address', $row['old_address'])->update(['address' => implode(', ', array_filter([$row['address'], $row['city'], $row['province']]))]);
            $school->where('name', $row['old_name'])->update(['name' => $row['name'], 'slug' => Str::slug($row['name'])]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('excluded_school_codes');
    }
};
