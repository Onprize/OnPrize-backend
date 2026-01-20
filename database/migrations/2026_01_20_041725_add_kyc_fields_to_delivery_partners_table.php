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
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->string('aadhaar_number', 12)->after('user_id')->nullable();
            $table->string('pan_number', 10)->after('aadhaar_number')->nullable();
            $table->text('address')->after('pan_number')->nullable();
            $table->string('bank_name')->after('address')->nullable();
            $table->string('account_number')->after('bank_name')->nullable();
            $table->string('ifsc_code')->after('account_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_partners', function (Blueprint $table) {
            $table->dropColumn([
                'aadhaar_number',
                'pan_number',
                'address',
                'bank_name',
                'account_number',
                'ifsc_code'
            ]);
        });
    }
};
