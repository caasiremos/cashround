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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('current_recipient_member_id')->nullable()->after('owner_id')->constrained('members')->nullOnDelete();
            $table->unsignedInteger('completed_circles')->default(0)->after('current_recipient_member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['current_recipient_member_id']);
            $table->dropColumn(['current_recipient_member_id', 'completed_circles']);
        });
    }
};
