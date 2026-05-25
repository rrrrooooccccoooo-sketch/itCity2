<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'access_profile')) {
                $table->string('access_profile', 60)->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'permission_overrides')) {
                $table->json('permission_overrides')->nullable()->after('access_profile');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permission_overrides')) {
                $table->dropColumn('permission_overrides');
            }

            if (Schema::hasColumn('users', 'access_profile')) {
                $table->dropColumn('access_profile');
            }
        });
    }
};
