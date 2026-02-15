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
        Schema::create('transaction_auths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_transaction_id')->constrained('wallet_transactions')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_chairperson_approved')->default(false);
            $table->boolean('has_treasurer_approved')->default(false);
            $table->boolean('has_secretary_approved')->default(false);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_auths');
    }
};
