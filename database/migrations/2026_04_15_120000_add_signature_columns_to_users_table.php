<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('signature_data_url')->nullable();
            $table->timestamp('signature_updated_at')->nullable();
            $table->string('signature_hash', 64)->nullable();
            $table->string('signature_last_ip', 45)->nullable();
            $table->string('signature_last_user_agent', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'signature_data_url',
                'signature_updated_at',
                'signature_hash',
                'signature_last_ip',
                'signature_last_user_agent',
            ]);
        });
    }
};
