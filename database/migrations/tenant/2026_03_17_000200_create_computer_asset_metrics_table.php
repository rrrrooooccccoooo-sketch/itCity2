<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('computer_asset_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_asset_id')->constrained('computer_assets')->cascadeOnDelete();
            $table->timestamp('captured_at');
            $table->decimal('cpu_usage_percent', 5, 2)->nullable();
            $table->decimal('memory_usage_percent', 5, 2)->nullable();
            $table->decimal('disk_usage_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('uptime_seconds')->nullable();
            $table->decimal('net_rx_kbps', 10, 2)->nullable();
            $table->decimal('net_tx_kbps', 10, 2)->nullable();
            $table->unsignedInteger('process_count')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['computer_asset_id', 'captured_at']);
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_asset_metrics');
    }
};
