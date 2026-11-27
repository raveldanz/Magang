<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedInteger('group_limit');
            $table->string('letter_contact', 120);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('registration_settings');
    }
};
