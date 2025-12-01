<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buy_for_me_requests', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->text('product_url')->nullable();
            $table->text('description');
            $table->decimal('estimated_price', 10, 2);
            $table->decimal('actual_price', 10, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->integer('quantity')->default(1);
            $table->text('shipping_address');
            $table->text('special_instructions')->nullable();
            $table->string('product_image_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'purchased', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_for_me_requests');
    }
};
