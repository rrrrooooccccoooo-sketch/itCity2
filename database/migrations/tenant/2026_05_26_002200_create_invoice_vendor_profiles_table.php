<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_key', 160)->unique();
            $table->string('supplier_name', 190);
            $table->json('known_brands')->nullable();
            $table->json('known_models')->nullable();
            $table->json('serial_prefixes')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_vendor_profiles');
    }
};
