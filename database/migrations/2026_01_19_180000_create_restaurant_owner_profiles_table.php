<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates a separate table for restaurant owner KYC/profile data.
     * This approach is safe and will NOT affect existing data:
     * - Creates a new table, doesn't modify existing ones
     * - Links to users via foreign key
     * - Existing users without profiles will simply have no entry here
     */
    public function up(): void
    {
        Schema::create('restaurant_owner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // KYC Documents
            $table->string('aadhaar_number', 12)->nullable();
            $table->string('pan_number', 10)->nullable();
            $table->string('gst_number', 15)->nullable(); // Optional GST
            $table->string('fssai_license', 20)->nullable(); // Food license
            
            // Business Address (for documents/legal purposes)
            $table->text('business_address')->nullable();
            $table->string('business_city', 100)->nullable();
            $table->string('business_state', 100)->nullable();
            $table->string('business_pincode', 10)->nullable();
            
            // Bank Details for payouts
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc', 11)->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            
            // Verification status
            $table->enum('kyc_status', ['pending', 'under_review', 'verified', 'rejected'])->default('pending');
            $table->text('kyc_rejection_reason')->nullable();
            $table->timestamp('kyc_verified_at')->nullable();
            
            // Document uploads (URLs)
            $table->string('aadhaar_front_image')->nullable();
            $table->string('aadhaar_back_image')->nullable();
            $table->string('pan_image')->nullable();
            $table->string('fssai_certificate_image')->nullable();
            $table->string('bank_passbook_image')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique('user_id');
            $table->index('aadhaar_number');
            $table->index('pan_number');
            $table->index('kyc_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_owner_profiles');
    }
};
