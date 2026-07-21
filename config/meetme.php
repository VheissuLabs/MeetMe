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

    'conference_name' => env('MEETME_CONFERENCE_NAME', 'Laracon'),

    'starts_at' => env('MEETME_STARTS_AT'),

    'ends_at' => env('MEETME_ENDS_AT'),

    /*
    |--------------------------------------------------------------------------
    | Icebreaker Questions
    |--------------------------------------------------------------------------
    |
    | One shared pool of generic AI-generated questions, created once via
    | `meetme:generate-questions`. The AI never receives user data. Scans
    | never wait on AI — they read from this pool synchronously.
    |
    */

    'question_count' => (int) env('MEETME_QUESTION_COUNT', 50),

    /*
    |--------------------------------------------------------------------------
    | Fallback Questions
    |--------------------------------------------------------------------------
    |
    | Used when a user's AI-generated pool is empty at scan time. A scan
    | never waits on AI — this pool guarantees the app works without an
    | Anthropic key at all.
    |
    */

    'fallback_questions' => [
        'What is the most cursed production bug they have ever shipped — and how did they find it?',
        'Ask them: tabs or spaces — and can they actually defend it?',
        'What is the one tool they could not live without right now?',
        'Which side project of theirs is currently gathering the most dust?',
        'What is the best conference talk they have ever seen live?',
        'If they had a free month to build anything at all, what would it be?',
        'What is their spiciest take about the framework or language they use every day?',
        'What is the weirdest thing they have ever automated?',
        'What tech opinion have they completely reversed on?',
        'What is the smallest change they have ever shipped with the biggest impact?',
    ],

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
