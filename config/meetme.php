<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conference
    |--------------------------------------------------------------------------
    |
    | MeetMe is event-agnostic: everything specific to the event you are
    | running lives here. The name feeds AI question generation and UI
    | branding. Dates drive recap scheduling and answer retention.
    |
    */

    'conference_name' => env('MEETME_CONFERENCE_NAME', 'Your Conference'),

    'starts_at' => env('MEETME_STARTS_AT'),

    'ends_at' => env('MEETME_ENDS_AT'),

    /*
    |--------------------------------------------------------------------------
    | Icebreaker Question Pools
    |--------------------------------------------------------------------------
    |
    | Questions are pre-generated per user so scans never wait on AI. When a
    | user's unconsumed pool drops below the watermark, a top-up job is
    | dispatched.
    |
    */

    'question_pool_size' => (int) env('MEETME_QUESTION_POOL_SIZE', 15),

    'pool_low_watermark' => (int) env('MEETME_POOL_LOW_WATERMARK', 3),

    /*
    |--------------------------------------------------------------------------
    | Scanning
    |--------------------------------------------------------------------------
    |
    | Maximum meeting-creation attempts per user per minute, throttling
    | QR-photo spam runs.
    |
    */

    'scan_rate_limit' => (int) env('MEETME_SCAN_RATE_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | Answer Retention
    |--------------------------------------------------------------------------
    |
    | When set, recorded answer text is hard-deleted this many days after
    | ends_at (after recap emails have gone out). Null keeps answers forever.
    |
    */

    'purge_answers_after_days' => transform(env('MEETME_PURGE_ANSWERS_AFTER_DAYS'), fn ($days) => (int) $days),

];
