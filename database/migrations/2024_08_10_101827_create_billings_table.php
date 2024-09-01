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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();            
            $table->decimal('amount', $precision = 12, $scale = 2);
            $table->string('payment_account')->nullable();
            $table->string('status')->default('unpaid');
            $table->string('billing_name')->nullable();
            $table->foreignId('billing_user_id');
            $table->foreignId('order_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
