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
        // 1. Tambah kolom visibilitas dan metrik interaksi pada internship_projects
        Schema::table('internship_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('internship_projects', 'is_public')) {
                $table->boolean('is_public')->default(true)->index();
            }
            if (!Schema::hasColumn('internship_projects', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->index();
            }
            if (!Schema::hasColumn('internship_projects', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->index();
            }
            if (!Schema::hasColumn('internship_projects', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0);
            }
        });

        // 2. Tabel project_likes: Dukungan Suka untuk user login maupun tamu publik
        if (!Schema::hasTable('project_likes')) {
            Schema::create('project_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internship_project_id')->constrained('internship_projects')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('guest_identifier', 64)->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['internship_project_id', 'user_id']);
                $table->index(['internship_project_id', 'guest_identifier']);
            });
        }

        // 3. Tabel project_comments: Komentar publik & terautentikasi dengan moderasi admin
        if (!Schema::hasTable('project_comments')) {
            Schema::create('project_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('internship_project_id')->constrained('internship_projects')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('author_name', 100);
                $table->string('author_role', 50)->nullable();
                $table->boolean('is_guest')->default(false);
                $table->text('comment');
                $table->unsignedInteger('likes_count')->default(0);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['internship_project_id', 'created_at']);
            });
        }

        // 4. Tabel project_comment_likes: Dukungan suka pada komentar
        if (!Schema::hasTable('project_comment_likes')) {
            Schema::create('project_comment_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_comment_id')->constrained('project_comments')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('guest_identifier', 64)->nullable()->index();
                $table->timestamps();

                $table->index(['project_comment_id', 'user_id']);
                $table->index(['project_comment_id', 'guest_identifier']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_comment_likes');
        Schema::dropIfExists('project_comments');
        Schema::dropIfExists('project_likes');

        Schema::table('internship_projects', function (Blueprint $table) {
            if (Schema::hasColumn('internship_projects', 'views_count')) {
                $table->dropColumn('views_count');
            }
            if (Schema::hasColumn('internship_projects', 'comments_count')) {
                $table->dropColumn('comments_count');
            }
            if (Schema::hasColumn('internship_projects', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
            if (Schema::hasColumn('internship_projects', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
