<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('node_probes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->cascadeOnDelete();
            $table->boolean('reachable')->default(false);
            $table->decimal('latency_ms', 8, 2)->nullable();
            $table->json('checked_ports')->nullable();
            $table->json('open_ports')->nullable();
            $table->string('message')->nullable();
            $table->timestamp('probed_at');
            $table->timestamps();

            $table->index(['node_id', 'probed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_probes');
    }
};
