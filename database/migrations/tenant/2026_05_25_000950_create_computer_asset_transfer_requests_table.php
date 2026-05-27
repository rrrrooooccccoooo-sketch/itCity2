<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('computer_asset_transfer_requests')) {
            return;
        }

        Schema::create('computer_asset_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_asset_id')->constrained('computer_assets', 'catr_asset_fk')->cascadeOnDelete();
            $table->string('status', 30)->default('pending');

            $table->foreignId('requested_by_user_id')->nullable()->constrained('users', 'catr_req_by_fk')->nullOnDelete();
            $table->string('requested_by_name', 120);
            $table->unsignedBigInteger('requested_from_branch_id')->nullable();
            $table->unsignedBigInteger('requested_to_branch_id');

            $table->foreign('requested_from_branch_id', 'catr_from_branch_fk')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('requested_to_branch_id', 'catr_to_branch_fk')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();

            $table->foreignId('requested_to_user_id')->nullable()->constrained('users', 'catr_req_to_fk')->nullOnDelete();
            $table->string('requested_to_user_name', 120);

            $table->text('reason');
            $table->text('note')->nullable();
            $table->timestamp('requested_at')->useCurrent();

            $table->foreignId('decided_by_user_id')->nullable()->constrained('users', 'catr_decided_by_fk')->nullOnDelete();
            $table->string('decided_by_name', 120)->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();

            $table->timestamps();

            $table->index(['computer_asset_id', 'status'], 'catr_asset_status_idx');
            $table->index(['requested_to_user_id', 'status'], 'catr_to_user_status_idx');
            $table->index(['requested_by_user_id', 'status'], 'catr_by_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_asset_transfer_requests');
    }
};
