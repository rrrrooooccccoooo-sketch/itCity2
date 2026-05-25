<?php

declare(strict_types=1);

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
            if (!Schema::hasColumn('users', 'signature_data_url')) {
                $table->longText('signature_data_url')->nullable();
            }

            if (!Schema::hasColumn('users', 'signature_updated_at')) {
                $table->timestamp('signature_updated_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'signature_hash')) {
                $table->string('signature_hash', 64)->nullable();
            }

            if (!Schema::hasColumn('users', 'signature_last_ip')) {
                $table->string('signature_last_ip', 45)->nullable();
            }

            if (!Schema::hasColumn('users', 'signature_last_user_agent')) {
                $table->string('signature_last_user_agent', 512)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropColumns = [];

            foreach ([
                'signature_data_url',
                'signature_updated_at',
                'signature_hash',
                'signature_last_ip',
                'signature_last_user_agent',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
