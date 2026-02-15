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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('destination_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type')->nullable();
            $table->decimal('amount', 19, 4);
            $table->decimal('service_fee', 19, 4)->default(0);
            $table->string('status')->nullable(); 
            $table->string('transaction_id')->nullable();// PENDING, SUCCESSFUL, FAILED
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transfer_transactions');
    }
};
