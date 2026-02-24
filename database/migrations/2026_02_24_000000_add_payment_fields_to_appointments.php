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
        Schema::table('appointments', function (Blueprint $blueprint) {
            $blueprint->string('payment_method')->nullable()->comment('cash, transfer, hybrid');
            $blueprint->decimal('cash_amount', 12, 2)->default(0);
            $blueprint->decimal('transfer_amount', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['payment_method', 'cash_amount', 'transfer_amount']);
        });
    }
};
