<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_brandings', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name', 120)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#0f172a');
            $table->string('accent_color', 7)->default('#38bdf8');
            $table->string('background_color', 7)->default('#f1f5f9');
            $table->string('text_color', 7)->default('#111827');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_brandings');
    }
};
