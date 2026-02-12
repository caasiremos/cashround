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
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_ledger_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->decimal('credit_record', 19, 4)->default(0);
            $table->decimal('debit_record', 19, 4)->default(0);
            $table->decimal('balance_before', 21, 2)->nullable();
            $table->decimal('balance_after', 21, 2)->nullable();
            $table->boolean('is_reversal')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('general_ledger_account_id');
            $table->index('journal_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
