<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('pronouns');
            $table->string('job_title')->nullable()->after('headline');
            $table->string('company')->nullable()->after('job_title');
            $table->boolean('answers_visible')->default(true)->after('email_visible');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['headline', 'job_title', 'company', 'answers_visible']);
        });
    }
};
