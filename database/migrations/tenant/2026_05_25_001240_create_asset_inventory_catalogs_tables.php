<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asset_equipment_type_catalogs')) {
            Schema::create('asset_equipment_type_catalogs', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 60)->unique();
                $table->string('label', 120);
                $table->string('description', 255)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $equipmentDefaults = [
                ['key' => 'desktop', 'label' => 'Desktop'],
                ['key' => 'laptop', 'label' => 'Laptop'],
                ['key' => 'server', 'label' => 'Server'],
                ['key' => 'workstation', 'label' => 'Workstation'],
                ['key' => 'aio', 'label' => 'All in One'],
                ['key' => 'thin-client', 'label' => 'Thin Client'],
                ['key' => 'monitor', 'label' => 'Monitor'],
                ['key' => 'headset', 'label' => 'Diadema'],
                ['key' => 'phone', 'label' => 'Telefono'],
                ['key' => 'other', 'label' => 'Otro'],
            ];

            foreach ($equipmentDefaults as $index => $item) {
                DB::table('asset_equipment_type_catalogs')->insert([
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'description' => null,
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (!Schema::hasTable('asset_status_catalogs')) {
            Schema::create('asset_status_catalogs', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 60)->unique();
                $table->string('label', 120);
                $table->string('description', 255)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $statusDefaults = [
                ['key' => 'in_use', 'label' => 'En uso'],
                ['key' => 'stock', 'label' => 'En stock'],
                ['key' => 'repair', 'label' => 'En reparacion'],
                ['key' => 'retired', 'label' => 'Retirado'],
            ];

            foreach ($statusDefaults as $index => $item) {
                DB::table('asset_status_catalogs')->insert([
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'description' => null,
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_status_catalogs');
        Schema::dropIfExists('asset_equipment_type_catalogs');
    }
};
