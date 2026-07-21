<?php

it('exposes every meetme config key', function () {
    expect(config('meetme'))->toHaveKeys([
        'conference_name',
        'starts_at',
        'ends_at',
        'question_pool_size',
        'pool_low_watermark',
        'scan_rate_limit',
        'purge_answers_after_days',
    ]);
});

it('has sane defaults without any env configuration', function () {
    expect(config('meetme.conference_name'))->toBe('Laracon')
        ->and(config('meetme.starts_at'))->toBeNull()
        ->and(config('meetme.ends_at'))->toBeNull()
        ->and(config('meetme.question_pool_size'))->toBe(15)
        ->and(config('meetme.pool_low_watermark'))->toBe(3)
        ->and(config('meetme.scan_rate_limit'))->toBe(10)
        ->and(config('meetme.purge_answers_after_days'))->toBeNull();
});

it('keeps the pool low watermark below the pool size', function () {
    expect(config('meetme.pool_low_watermark'))
        ->toBeLessThan(config('meetme.question_pool_size'));
});
