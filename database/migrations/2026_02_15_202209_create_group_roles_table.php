<?php

use App\Models\GroupRole;
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
        Schema::create('group_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('role', [GroupRole::CHAIRPERSON, GroupRole::MEMBER, GroupRole::TREASURER, GroupRole::SECRETARY])->default(GroupRole::MEMBER);
            $table->timestamps();
            $table->unique(['group_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_roles');
    }
};
