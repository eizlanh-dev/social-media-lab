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
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('wallet_balance', 12, 4)->default(0)->after('password');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('provider_cost', 12, 4)->nullable()->after('charge');
            $table->decimal('customer_price', 12, 4)->nullable()->after('provider_cost');
            $table->decimal('profit', 12, 4)->nullable()->after('customer_price');
            $table->string('order_channel', 32)->default('internal')->after('profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['provider_cost', 'customer_price', 'profit', 'order_channel']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('wallet_balance');
        });
    }
};
