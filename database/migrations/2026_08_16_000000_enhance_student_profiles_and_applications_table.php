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
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'university_id')) {
                $table->foreignId('university_id')->nullable()->after('universitas')->constrained('universities')->nullOnDelete();
            }
            if (!Schema::hasColumn('student_profiles', 'faculty')) {
                $table->string('faculty')->nullable()->after('university_id');
            }
            if (!Schema::hasColumn('student_profiles', 'fakultas')) {
                $table->string('fakultas')->nullable()->after('faculty');
            }
            if (!Schema::hasColumn('student_profiles', 'major')) {
                $table->string('major')->nullable()->after('jurusan');
            }
            if (!Schema::hasColumn('student_profiles', 'semester')) {
                $table->string('semester')->nullable()->after('major');
            }
            if (!Schema::hasColumn('student_profiles', 'address')) {
                $table->text('address')->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('student_profiles', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('student_profiles', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'proposal_letter_path')) {
                $table->string('proposal_letter_path')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('applications', 'cv_path')) {
                $table->string('cv_path')->nullable()->after('proposal_letter_path');
            }
            if (!Schema::hasColumn('applications', 'transcript_path')) {
                $table->string('transcript_path')->nullable()->after('cv_path');
            }
            if (!Schema::hasColumn('applications', 'id_card_path')) {
                $table->string('id_card_path')->nullable()->after('transcript_path');
            }
        });

        Schema::table('final_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('final_reports', 'final_report_path')) {
                $table->string('final_report_path')->nullable()->after('file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn([
                'university_id',
                'faculty',
                'fakultas',
                'major',
                'semester',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_letter_path',
                'cv_path',
                'transcript_path',
                'id_card_path',
            ]);
        });

        Schema::table('final_reports', function (Blueprint $table) {
            $table->dropColumn(['final_report_path']);
        });
    }
};
