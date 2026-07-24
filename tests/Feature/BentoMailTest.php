<?php

use Illuminate\Support\Facades\Mail;

it('registers a bento mailer backed by the bento transport', function () {
    expect(config('mail.mailers.bento.transport'))->toBe('bento');
});

it('can resolve the bento mailer', function () {
    expect(Mail::mailer('bento'))->not->toBeNull();
});
