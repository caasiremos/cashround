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
        Schema::table('group_member', function (Blueprint $table) {
            $table->unsignedInteger('rotation_position')->default(0)->after('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_member', function (Blueprint $table) {
            $table->dropColumn('rotation_position');
        });
    }
};
