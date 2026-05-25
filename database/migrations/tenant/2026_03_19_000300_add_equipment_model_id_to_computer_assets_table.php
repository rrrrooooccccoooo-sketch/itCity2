<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->foreignId('equipment_model_id')
                ->nullable()
                ->after('node_id')
                ->constrained('equipment_models')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('computer_assets', function (Blueprint $table) {
            $table->dropForeign(['equipment_model_id']);
            $table->dropColumn('equipment_model_id');
        });
    }
};
