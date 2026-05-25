<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->decimal('layout_x', 8, 2)->nullable()->after('ip_address');
            $table->decimal('layout_y', 8, 2)->nullable()->after('layout_x');
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['layout_x', 'layout_y']);
        });
    }
};
