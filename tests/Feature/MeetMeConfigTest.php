<?php

it('exposes every meetme config key', function () {
    expect(config('meetme'))->toHaveKeys([
        'conference_name',
        'starts_at',
        'ends_at',
        'question_count',
        'fallback_questions',
        'scan_rate_limit',
        'purge_answers_after_days',
    ]);
});

it('has sane defaults without any env configuration', function () {
    expect(config('meetme.conference_name'))->toBe('Laracon')
        ->and(config('meetme.starts_at'))->toBeNull()
        ->and(config('meetme.ends_at'))->toBeNull()
        ->and(config('meetme.question_count'))->toBe(50)
        ->and(config('meetme.scan_rate_limit'))->toBe(10)
        ->and(config('meetme.purge_answers_after_days'))->toBeNull();
});
