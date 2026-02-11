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
        Schema::create('general_ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete()->nullable();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete()->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('account_type', ['asset', 'liability', 'income', 'expense', 'equity'])->default('asset');
            $table->timestamps();
            $table->softDeletes();
            $table->index('group_id');
            $table->index('slug');
            $table->index('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_accounts');
    }
};
