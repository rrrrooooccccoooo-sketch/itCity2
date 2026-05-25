<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floor_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('physical_space_id')->nullable()->constrained('physical_spaces')->onDelete('set null');
            $table->string('name', 140);
            $table->string('file_path')->unique();
            $table->string('file_type', 20)->nullable();
            $table->string('mime_type', 80)->nullable();
            $table->json('overlay_points')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index('branch_id');
            $table->index('physical_space_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floor_plans');
    }
};
