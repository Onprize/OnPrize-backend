<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            $table->enum('vehicle_type', ['bike', 'scooter', 'bicycle', 'car']);
            $table->string('vehicle_number', 50);
            $table->string('license_number', 50);
            $table->string('license_image')->nullable();
            $table->string('vehicle_rc_image')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            
            $table->boolean('is_available')->default(false);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();
            
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('is_available');
            $table->index(['current_latitude', 'current_longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_partners');
    }
};
