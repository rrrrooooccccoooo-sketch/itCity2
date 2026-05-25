<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('computer_asset_transfer_requests')) {
            return;
        }

        Schema::table('computer_asset_transfer_requests', function (Blueprint $table): void {
            if (!Schema::hasColumn('computer_asset_transfer_requests', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('computer_asset_transfer_requests')) {
            return;
        }

        Schema::table('computer_asset_transfer_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('computer_asset_transfer_requests', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
