<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('node_observed_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('node_id')->constrained('nodes')->cascadeOnDelete();
            $table->string('observed_via', 60)->default('manual');
            $table->string('mac_address', 17)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('hostname')->nullable();
            $table->string('domain_name')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('ssid')->nullable();
            $table->string('switch_port', 120)->nullable();
            $table->string('device_type', 80)->nullable();
            $table->boolean('is_managed')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['node_id', 'last_seen_at']);
            $table->index(['branch_id', 'last_seen_at']);
            $table->index(['node_id', 'mac_address']);
            $table->index(['node_id', 'hostname']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_observed_devices');
    }
};