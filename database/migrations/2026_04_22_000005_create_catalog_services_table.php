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
        Schema::create('catalog_services', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('external_service_id')->unique();
            $table->string('name');
            $table->string('category')->nullable()->index();
            $table->string('type')->nullable();
            $table->unsignedInteger('min')->default(1);
            $table->unsignedInteger('max')->default(1);
            $table->decimal('raw_rate', 12, 6)->default(0);
            $table->decimal('sell_rate', 12, 6)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_services');
    }
};
