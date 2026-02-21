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
        Schema::create('momo_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type')->nullable();
            $table->string('phone_number');
            $table->decimal('amount', 19, 4);
            $table->string('telco_provider');
            $table->decimal('provider_fee', 19, 4)->default(0);
            $table->decimal('service_fee', 19, 4)->default(0);
            $table->string('internal_status');
            $table->string('internal_id')->nullable();
            $table->string('external_status')->nullable();
            $table->string('external_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('momo_transactions');
    }
};
