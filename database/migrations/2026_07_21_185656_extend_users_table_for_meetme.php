<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();

            $table->ulid('qr_token')->unique()->after('remember_token');
            $table->unsignedBigInteger('github_id')->nullable()->unique()->after('qr_token');
            $table->string('github_username')->nullable()->after('github_id');
            $table->string('x_id')->nullable()->unique()->after('github_username');
            $table->string('x_username')->nullable()->after('x_id');
            $table->string('bluesky_handle')->nullable()->after('x_username');
            $table->string('avatar_url')->nullable()->after('bluesky_handle');
            $table->string('avatar_source')->default('github')->after('avatar_url');
            $table->string('pronouns', 30)->nullable()->after('avatar_source');
            $table->boolean('email_visible')->default(false)->after('pronouns');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'qr_token',
                'github_id',
                'github_username',
                'x_id',
                'x_username',
                'bluesky_handle',
                'avatar_url',
                'avatar_source',
                'pronouns',
                'email_visible',
            ]);

            $table->string('password')->nullable(false)->change();
        });
    }
};
