<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('computer_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('node_id')->nullable()->constrained('nodes')->nullOnDelete();
            $table->string('equipment_type')->default('desktop');
            $table->string('asset_tag')->nullable();
            $table->string('hostname')->nullable();
            $table->string('assigned_user')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('cpu')->nullable();
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->string('storage_type')->nullable();
            $table->unsignedInteger('storage_gb')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('office_version')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->string('status')->default('in_use');
            $table->text('notes')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index('asset_tag');
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_assets');
    }
};
