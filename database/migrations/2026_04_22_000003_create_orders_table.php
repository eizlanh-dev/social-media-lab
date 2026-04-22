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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('external_order_id')->nullable()->index();
            $table->unsignedBigInteger('service_id');
            $table->string('link', 2048);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('runs')->nullable();
            $table->unsignedInteger('interval')->nullable();
            $table->string('status')->nullable()->index();
            $table->string('charge')->nullable();
            $table->string('start_count')->nullable();
            $table->string('remains')->nullable();
            $table->string('currency', 12)->nullable();
            $table->string('refill_status')->nullable();
            $table->string('cancel_status')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('refill_requested_at')->nullable();
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('last_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
