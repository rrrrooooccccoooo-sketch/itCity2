<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('details');
            $table->decimal('last_cpu_usage_percent', 5, 2)->nullable()->after('last_seen_at');
            $table->decimal('last_memory_usage_percent', 5, 2)->nullable()->after('last_cpu_usage_percent');
            $table->decimal('last_disk_usage_percent', 5, 2)->nullable()->after('last_memory_usage_percent');
            $table->unsignedBigInteger('last_uptime_seconds')->nullable()->after('last_disk_usage_percent');

            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn([
                'last_seen_at',
                'last_cpu_usage_percent',
                'last_memory_usage_percent',
                'last_disk_usage_percent',
                'last_uptime_seconds',
            ]);
        });
    }
};
