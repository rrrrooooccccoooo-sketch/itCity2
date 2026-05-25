<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'auth_source')) {
                    $table->string('auth_source', 20)->default('local')->after('password');
                }

                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('auth_source');
                }
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_active')) {
                    $table->dropColumn('is_active');
                }

                if (Schema::hasColumn('users', 'auth_source')) {
                    $table->dropColumn('auth_source');
                }
            });
        }

        if (Schema::hasTable('password_reset_tokens')) {
            Schema::drop('password_reset_tokens');
        }
    }
};
