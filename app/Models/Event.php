<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @mixin IdeHelperEvent */
class Event extends Model
{
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
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => config('meetme.conference_name'),
            'starts_at' => config('meetme.starts_at'),
            'ends_at' => config('meetme.ends_at'),
        ]);
    }
}
