<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icebreaker_questions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'meeting_id']);
            $table->dropUnique(['meeting_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::table('icebreaker_questions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->ulid('meeting_id')->nullable()->unique();
            $table->index(['user_id', 'meeting_id']);
        });
    }
};
