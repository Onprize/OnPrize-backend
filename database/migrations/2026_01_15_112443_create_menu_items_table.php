<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('menu_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discount_price', 8, 2)->nullable();
            
            $table->boolean('is_veg')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            
            $table->unsignedInteger('calories')->nullable();
            $table->string('preparation_time', 50)->nullable();
            
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('restaurant_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
