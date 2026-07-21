<?php

namespace App\Models;

use Database\Factories\IcebreakerQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin IdeHelperIcebreakerQuestion */
#[UseFactory(IcebreakerQuestionFactory::class)]
class IcebreakerQuestion extends Model
{
    /** @use HasFactory<IcebreakerQuestionFactory> */
    use HasFactory, HasUlids;

    /** @var list<string> */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function unconsumed(Builder $query): void
    {
        $query->whereNull('meeting_id');
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function for(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    public static function randomUnconsumedFor(User $user): ?self
    {
        return self::query()
            ->for($user)
            ->unconsumed()
            ->inRandomOrder()
            ->first();
    }
}
