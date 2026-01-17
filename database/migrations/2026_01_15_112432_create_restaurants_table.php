<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('email');
            $table->string('phone', 20);
            
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            $table->json('cuisine_types')->nullable();
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->string('delivery_time', 50)->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->decimal('min_order_amount', 8, 2)->default(0.00);
            
            $table->boolean('is_open')->default(true);
            $table->boolean('is_accepting_orders')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            
            $table->decimal('commission_rate', 5, 2)->default(15.00);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('owner_id');
            $table->index('slug');
            $table->index('status');
            $table->index(['latitude', 'longitude']);
            $table->index('is_open');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
