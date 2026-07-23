<?php

namespace App\Models;

use App\Enums\AvatarSource;
use App\Enums\SocialProvider;
use App\Observers\UserObserver;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/** @mixin IdeHelperUser */
#[UseFactory(UserFactory::class)]
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'avatar_source' => AvatarSource::class,
            'email_visible' => 'boolean',
        ];
    }

    /** @return HasMany<SocialAccount, $this> */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return HasOne<SocialAccount, $this> */
    public function githubAccount(): HasOne
    {
        return $this->hasOne(SocialAccount::class)->where('provider', SocialProvider::Github);
    }

    /** @return HasOne<SocialAccount, $this> */
    public function xAccount(): HasOne
    {
        return $this->hasOne(SocialAccount::class)->where('provider', SocialProvider::X);
    }

    public function score(): int
    {
        return Meeting::query()->confirmed()->involving($this)->count();
    }

    public function averageAnswerRating(): ?float
    {
        $average = Meeting::query()
            ->confirmed()
            ->where('initiator_id', $this->id)
            ->whereNotNull('rating')
            ->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }
}
