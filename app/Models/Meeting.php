<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use App\Observers\MeetingObserver;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin IdeHelperMeeting */
#[UseFactory(MeetingFactory::class)]
#[ObservedBy(MeetingObserver::class)]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory, HasUlids;

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
            'status' => MeetingStatus::class,
            'answered_at' => 'datetime',
            'resolved_at' => 'datetime',
            'answer_redacted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /** @return BelongsTo<IcebreakerQuestion, $this> */
    public function icebreakerQuestion(): BelongsTo
    {
        return $this->belongsTo(IcebreakerQuestion::class);
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function confirmed(Builder $query): void
    {
        $query->where('status', MeetingStatus::Confirmed);
    }

    /** @param Builder<self> $query */
    #[Scope]
    protected function involving(Builder $query, User $user): void
    {
        $query->where(fn (Builder $query) => $query
            ->where('initiator_id', $user->id)
            ->orWhere('recipient_id', $user->id));
    }

    public static function pairKeyFor(User $one, User $two): string
    {
        return min($one->id, $two->id).':'.max($one->id, $two->id);
    }

    public static function between(User $one, User $two): ?self
    {
        return self::query()->firstWhere('pair_key', self::pairKeyFor($one, $two));
    }
}
