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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
              $table->binary('transactable_id', 16);
            $table->string('transactable_type')->index();
            $table->timestamp('transaction_date')->index();
            $table->string('transaction_name')->nullable();
            $table->decimal('amount', 19, 4);
            $table->boolean('is_reversed')->default(false)->index();
            $table->binary('reversed_journal_entry_id', 16)->nullable()->index();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
