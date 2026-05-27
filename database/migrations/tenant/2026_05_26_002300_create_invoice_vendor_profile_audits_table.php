<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_vendor_profile_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_vendor_profile_id')->nullable();
            $table->string('supplier_key', 160);
            $table->string('supplier_name', 190)->nullable();
            $table->string('action', 60);
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->string('changed_by_name', 120)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['supplier_key', 'created_at']);
            $table->index('action');
            $table->index('changed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_vendor_profile_audits');
    }
};
