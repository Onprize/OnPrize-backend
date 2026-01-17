<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_partner_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->foreignId('delivery_address_id')->constrained('user_addresses');
            $table->text('delivery_instructions')->nullable();
            
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->decimal('tax', 8, 2)->default(0.00);
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'ready_for_pickup',
                'picked_up', 'on_the_way', 'delivered', 'cancelled', 'refunded'
            ])->default('pending');
            
            $table->enum('payment_method', ['cash', 'card', 'upi', 'wallet']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_id')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->unsignedTinyInteger('user_rating')->nullable();
            $table->text('user_review')->nullable();
            $table->unsignedTinyInteger('restaurant_rating')->nullable();
            $table->unsignedTinyInteger('delivery_rating')->nullable();
            
            $table->timestamps();
            
            $table->index('order_number');
            $table->index('user_id');
            $table->index('restaurant_id');
            $table->index('delivery_partner_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
