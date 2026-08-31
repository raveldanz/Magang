<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->text('address')->nullable()->after('code');
            $table->string('phone')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
            $table->string('pic_name')->nullable()->after('email');
            $table->string('pic_nip')->nullable()->after('pic_name');
            $table->string('pic_position')->nullable()->after('pic_nip');
            $table->string('logo')->nullable()->after('pic_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'phone',
                'email',
                'pic_name',
                'pic_nip',
                'pic_position',
                'logo',
            ]);
        });
    }
};
