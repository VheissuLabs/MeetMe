<?php

namespace App\Models;

use Database\Factories\IcebreakerQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/** @mixin IdeHelperIcebreakerQuestion */
#[UseFactory(IcebreakerQuestionFactory::class)]
class IcebreakerQuestion extends Model
{
    /** @use HasFactory<IcebreakerQuestionFactory> */
    use HasFactory, HasUlids, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('question')
            ->logOnly(['question'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event): string => "Question {$event}");
    }

    /** @var list<string> */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /** @param array<int, string> $ids */
    public static function randomExcluding(array $ids = []): ?self
    {
        return self::query()
            ->whereNotIn('id', $ids)
            ->inRandomOrder()
            ->first();
    }
}
