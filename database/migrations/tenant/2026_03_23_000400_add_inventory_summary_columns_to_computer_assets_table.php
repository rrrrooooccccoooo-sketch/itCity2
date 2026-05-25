<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->timestamp('inventory_last_captured_at')->nullable()->after('last_uptime_seconds');
            $table->string('domain_name')->nullable()->after('inventory_last_captured_at');
            $table->string('bios_version')->nullable()->after('domain_name');
            $table->string('motherboard_product')->nullable()->after('bios_version');
            $table->string('motherboard_serial')->nullable()->after('motherboard_product');
            $table->string('antivirus_summary')->nullable()->after('motherboard_serial');
            $table->unsignedInteger('installed_programs_count')->nullable()->after('antivirus_summary');
            $table->unsignedInteger('hotfix_count')->nullable()->after('installed_programs_count');

            $table->index('inventory_last_captured_at');
        });
    }

    public function down(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->dropIndex(['inventory_last_captured_at']);
            $table->dropColumn([
                'inventory_last_captured_at',
                'domain_name',
                'bios_version',
                'motherboard_product',
                'motherboard_serial',
                'antivirus_summary',
                'installed_programs_count',
                'hotfix_count',
            ]);
        });
    }
};