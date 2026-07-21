<?php

namespace Database\Factories;

use App\Enums\AvatarSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function withoutPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

    public function withGithub(): static
    {
        return $this->has(SocialAccountFactory::new()->github(), 'socialAccounts')
            ->state(fn (array $attributes) => [
                'avatar_source' => AvatarSource::Github,
            ]);
    }

    public function withX(): static
    {
        return $this->has(SocialAccountFactory::new()->x(), 'socialAccounts');
    }

    public function withSocials(): static
    {
        return $this->withX()->state(fn (array $attributes) => [
            'bluesky_handle' => fake()->unique()->userName().'.bsky.social',
            'pronouns' => fake()->randomElement(['she/her', 'he/him', 'they/them', 'he/they', 'she/they']),
        ]);
    }
}
