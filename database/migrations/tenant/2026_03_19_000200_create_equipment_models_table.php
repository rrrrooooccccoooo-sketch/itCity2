<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('equipment_brands')->cascadeOnDelete();
            $table->string('equipment_type', 60)->default('other');
            $table->string('name', 120);
            // AP-specific RF specs
            $table->decimal('coverage_radius_min_m', 7, 2)->nullable();
            $table->decimal('coverage_radius_max_m', 7, 2)->nullable();
            $table->smallInteger('default_signal_dbm')->nullable();
            $table->string('radiation_pattern', 40)->nullable();
            $table->decimal('mount_height_m', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['brand_id', 'equipment_type', 'name']);
            $table->index('equipment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};
