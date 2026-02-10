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
            $table->foreignId('transaction_type_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_name')->nullable();
            $table->string('phone_number');
            $table->decimal('amount', 19, 4);
            $table->decimal('transaction_fee', 19, 4)->default(0);
            $table->decimal('service_fee', 19, 4)->default(0);
            $table->string('status'); // PENDING, SUCCESSFUL, FAILED
            $table->string('telco_transaction_id')->nullable();
            $table->string('external_id')->unique();
            $table->text('error_message')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
