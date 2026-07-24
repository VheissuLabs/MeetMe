<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('meetme:make-admin {email : The email of the user to promote} {--revoke : Remove admin access instead}')]
#[Description('Grant (or revoke) admin panel access for a user')]
class MakeAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::query()->firstWhere('email', $email);

        if ($user === null) {
            $this->error("No user found with email {$email}.");

            return self::FAILURE;
        }

        $revoke = (bool) $this->option('revoke');
        $user->update(['is_admin' => ! $revoke]);

        $this->info($revoke
            ? "{$user->name} ({$email}) is no longer an admin."
            : "{$user->name} ({$email}) is now an admin.");

        return self::SUCCESS;
    }
}
