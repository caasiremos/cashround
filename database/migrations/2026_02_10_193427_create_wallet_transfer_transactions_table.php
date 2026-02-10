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
        Schema::create('wallet_transfer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('destination_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_general_ledger_account_id')->constrained('general_ledger_accounts')->cascadeOnDelete();
            $table->foreignId('destination_general_ledger_account_id')->constrained('general_ledger_accounts')->cascadeOnDelete();
            $table->decimal('amount', 19, 4);
            $table->decimal('transaction_fee', 19, 4)->default(0);
            $table->decimal('service_fee', 19, 4)->default(0);
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
