<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->string('primary_ip_address')->nullable()->after('hotfix_count');
            $table->string('primary_mac_address')->nullable()->after('primary_ip_address');
            $table->string('operating_system_version')->nullable()->after('primary_mac_address');
            $table->string('operating_system_build')->nullable()->after('operating_system_version');
            $table->string('primary_gpu')->nullable()->after('operating_system_build');
            $table->unsignedSmallInteger('memory_modules_count')->nullable()->after('primary_gpu');
            $table->unsignedSmallInteger('physical_disks_count')->nullable()->after('memory_modules_count');
        });
    }

    public function down(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->dropColumn([
                'primary_ip_address',
                'primary_mac_address',
                'operating_system_version',
                'operating_system_build',
                'primary_gpu',
                'memory_modules_count',
                'physical_disks_count',
            ]);
        });
    }
};