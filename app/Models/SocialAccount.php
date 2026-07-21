<?php

namespace App\Models;

use App\Enums\SocialProvider;
use Database\Factories\SocialAccountFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin IdeHelperSocialAccount */
#[UseFactory(SocialAccountFactory::class)]
class SocialAccount extends Model
{
    /** @use HasFactory<SocialAccountFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'provider' => SocialProvider::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
