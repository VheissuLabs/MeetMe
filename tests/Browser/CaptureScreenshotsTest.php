<?php

use App\Actions\AwardAchievements;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Database\Seeders\MeetMeDemoSeeder;
use Illuminate\Support\Facades\Artisan;

it('captures marketing screenshots on a phone (light + dark)', function () {
    Artisan::call('migrate:fresh', ['--force' => true]);
    $this->seed(MeetMeDemoSeeder::class);

    // Light up the badges everyone has already earned (the seed creates
    // confirmed meetings directly, which doesn't trigger the observer).
    $awarder = app(AwardAchievements::class);
    User::query()->each(fn (User $user) => $awarder->for($user));

    $explorer = User::query()->firstWhere('email', 'test@example.com');

    // Deterministic meetings for the answer + rating screens, with fresh
    // partners so they never collide with a seeded pairing.
    $partnerA = User::factory()->create(['name' => 'Jordan Lee', 'pronouns' => 'they/them']);
    $partnerB = User::factory()->create(['name' => 'Sam Rivera', 'pronouns' => 'she/her']);

    $answering = Meeting::factory()->create([
        'initiator_id' => $explorer->id,
        'recipient_id' => $partnerA->id,
        'status' => MeetingStatus::Pending,
        'question' => 'What is the most cursed production bug you have ever shipped — and how did you find it?',
    ]);

    $rating = Meeting::factory()->answered()->create([
        'initiator_id' => $partnerB->id,
        'recipient_id' => $explorer->id,
        'question' => 'What is the one tool you could not live without right now?',
        'answer' => 'Honestly? ripgrep. It quietly changed how I move through every codebase.',
    ]);

    $connection = Meeting::query()->confirmed()->involving($explorer)->first();
    $profileToken = $connection === null
        ? null
        : ($connection->initiator_id === $explorer->id ? $connection->recipient : $connection->initiator)->qr_token;

    // Open a phone-sized page, optionally in dark mode.
    $open = function (string $url, bool $dark) {
        $page = visit($url)->on()->iPhone14Pro();

        return $dark ? $page->inDarkMode() : $page;
    };

    // Authenticated app screens, captured for one theme. The login session
    // persists across visits, so we only sign in once (below).
    $shootApp = function (bool $dark, string $suffix) use ($open, $answering, $rating, $profileToken) {
        $open('/my-qr', $dark)->assertPathIs('/my-qr')->screenshot(false, "my-qr{$suffix}");
        $open('/connections', $dark)->screenshot(false, "connections{$suffix}");
        $open('/leaderboard', $dark)->screenshot(false, "leaderboard{$suffix}");
        $open('/scan', $dark)->screenshot(false, "scan{$suffix}");

        $open('/meetings/'.$answering->id, $dark)
            ->fill('answer', 'A race condition in a cron job that only fired during DST — found it by staring at timestamps at 2am.')
            ->screenshot(false, "answering{$suffix}");

        $open('/meetings/'.$rating->id, $dark)
            ->click('@star-4')
            ->screenshot(false, "rating{$suffix}");

        if ($profileToken !== null) {
            $open('/users/'.$profileToken, $dark)->screenshot(false, "profile{$suffix}");
        }
    };

    // Landing (public) — captured before signing in so the sign-in options show.
    $open('/', false)->screenshot(false, 'landing');
    $open('/', true)->screenshot(false, 'landing-dark');

    // Sign in once; the session carries across the rest of the visits.
    $open('/login', false)
        ->fill('email', 'test@example.com')
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathIs('/my-qr');

    $shootApp(false, '');
    $shootApp(true, '-dark');

    expect(true)->toBeTrue();
});
