<?php

namespace App\Models;

use Database\Factories\IcebreakerQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /** @param list<string> $ids */
    public static function randomExcluding(array $ids = []): ?self
    {
        return self::query()
            ->whereNotIn('id', $ids)
            ->inRandomOrder()
            ->first();
    }
}
