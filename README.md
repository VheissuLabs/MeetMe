# MeetMe

> Turn "so, what do you do?" into a game.

MeetMe is an open-source conference networking game. Every attendee gets a QR code and
AI-written icebreakers. Scan someone, ask their question, and you both score. A live
leaderboard ranks the room, a connections list becomes your follow-up, and everyone gets a
recap email when the event ends.

Built with Laravel 13, Inertia v3 + Vue 3, Reverb, and Tailwind. MIT licensed — fork it and
run it for your own event by editing `.env`, not code.

## Screenshots

_Coming soon — captured from the real app (dashboard QR, question screen, live leaderboard)._

## How it works

1. **Scan.** Point your camera at someone's MeetMe QR (in-app, or your phone's native camera
   via the `/meet/{token}` deep-link).
2. **Ask.** You get an AI-written icebreaker — _"What's the most cursed production bug they've
   ever shipped?"_ — ask it out loud and type their answer.
3. **Score.** They rate how well you captured it (rating _is_ the confirmation) and you both
   get a point. The leaderboard updates live.

## Quick start

Requires PHP 8.4+, Node 22+, and Composer.

```bash
git clone https://github.com/VheissuLabs/MeetMe.git
cd MeetMe
composer setup          # install deps, create .env, key, migrate, build assets
php artisan migrate --seed   # optional: ~30 demo users and a believable leaderboard
composer dev            # serve app + queue + Vite + SSR in one command
```

Then sign in as the demo user: **test@example.com** / **password**.

### Runs without an API key

The app is fully functional **without an Anthropic API key**. Icebreaker questions fall back
to a curated pool in `config/meetme.php`, so scanning, scoring, and every screen work out of
the box. Add `ANTHROPIC_API_KEY` and run `php artisan meetme:generate-questions` to generate a
fresh AI pool when you want one.

## Configuration

Everything event-specific lives in `config/meetme.php`, overridable via `.env` (`MEETME_*`):
conference name, event dates, question pool size, scan rate limit, and answer-retention
window. Fork, change `.env`, done.

## Database

Local development defaults to **SQLite** (zero setup). Production and CI run on **MySQL** —
that's what the app is tested against on every PR.

## Commands

| Command | What it does |
|---|---|
| `php artisan meetme:generate-questions` | Build the global AI icebreaker pool (needs `ANTHROPIC_API_KEY`; `--fresh` regenerates) |
| `php artisan meetme:send-recaps` | Email every attendee their post-event recap |
| `php artisan meetme:purge-answers` | Hard-delete recorded answer text after the retention window (scheduled daily) |

## Testing

```bash
composer lint    # Pint + Prettier + docblock formatting (fixes in place)
composer ci:check   # the full gate: lint, PHPStan, vue-tsc, Pest (arch presets included)
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Security issues: [SECURITY.md](SECURITY.md).

## License

MIT — see [LICENSE](LICENSE).
