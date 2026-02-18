<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores an override for when this member's cashround is scheduled (e.g. after rescheduling when their date passed).
     */
    public function up(): void
    {
        Schema::table('group_member', function (Blueprint $table) {
            $table->date('scheduled_cashround_date')->nullable()->after('rotation_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_member', function (Blueprint $table) {
            $table->dropColumn('scheduled_cashround_date');
        });
    }
};
