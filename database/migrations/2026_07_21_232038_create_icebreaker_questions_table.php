<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icebreaker_questions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->ulid('meeting_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['user_id', 'meeting_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icebreaker_questions');
    }
};
