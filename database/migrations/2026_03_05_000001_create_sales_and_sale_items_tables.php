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
        Schema::create('sales', function (Blueprint $create) {
            $create->id();
            $create->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $create->string('customer_name')->nullable();
            $create->string('customer_phone')->nullable();
            $create->decimal('total', 10, 2)->default(0);
            $create->enum('payment_method', ['cash', 'transfer', 'hybrid'])->default('cash');
            $create->decimal('cash_amount', 10, 2)->default(0);
            $create->decimal('transfer_amount', 10, 2)->default(0);
            $create->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $create) {
            $create->id();
            $create->foreignId('sale_id')->constrained()->onDelete('cascade');
            $create->foreignId('product_id')->constrained()->onDelete('cascade');
            $create->integer('quantity');
            $create->decimal('unit_price', 10, 2);
            $create->decimal('subtotal', 10, 2);
            $create->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
