<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('floor_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('physical_space_id')->nullable()->constrained('physical_spaces')->nullOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type', 12);
            $table->string('mime_type', 120)->nullable();
            $table->json('overlay_points')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'physical_space_id']);
            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_plans');
    }
};
