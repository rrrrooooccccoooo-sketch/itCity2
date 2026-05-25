<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('node_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_node_id')->constrained('nodes')->cascadeOnDelete();
            $table->foreignId('to_node_id')->constrained('nodes')->cascadeOnDelete();
            $table->string('relation_type')->default('linked_to');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['from_node_id', 'to_node_id', 'relation_type'], 'node_relations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_relations');
    }
};
