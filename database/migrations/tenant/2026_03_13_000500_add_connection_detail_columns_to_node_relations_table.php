<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('node_relations', function (Blueprint $table) {
            $table->string('from_endpoint', 120)->nullable()->after('relation_type');
            $table->string('to_endpoint', 120)->nullable()->after('from_endpoint');
            $table->boolean('is_inter_campus')->default(false)->after('to_endpoint');
            $table->string('vpn_profile', 120)->nullable()->after('is_inter_campus');
        });
    }

    public function down(): void
    {
        Schema::table('node_relations', function (Blueprint $table) {
            $table->dropColumn([
                'from_endpoint',
                'to_endpoint',
                'is_inter_campus',
                'vpn_profile',
            ]);
        });
    }
};
