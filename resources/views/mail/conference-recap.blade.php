<x-mail::message>
# Thanks for playing, {{ $user->name }}!

Here's your {{ $conferenceName }} in one place — everyone you met and how to keep in touch.

**{{ $total }}** {{ Str::plural('connection', $total) }}
@if ($position)
&nbsp;·&nbsp; Finished **#{{ $position }}** on the leaderboard
@endif
@if ($averageRating)
&nbsp;·&nbsp; Your answers averaged **{{ $averageRating }}★** — people felt heard
@endif

## Your connections

@foreach ($connections as $connection)
---

**{{ $connection['name'] }}**@if ($connection['pronouns']) ({{ $connection['pronouns'] }})@endif

@if (! empty($connection['socials']) || ! empty($connection['email']))
@php
$links = [];
if (isset($connection['socials']['github'])) { $links[] = '[GitHub]('.$connection['socials']['github'].')'; }
if (isset($connection['socials']['x'])) { $links[] = '[X]('.$connection['socials']['x'].')'; }
if (isset($connection['socials']['bluesky'])) { $links[] = '[Bluesky]('.$connection['socials']['bluesky'].')'; }
if (isset($connection['email'])) { $links[] = '[Email](mailto:'.$connection['email'].')'; }
@endphp
{!! implode(' &nbsp;·&nbsp; ', $links) !!}
@endif

> {{ $connection['question'] }}
>
@if ($connection['answerRedacted'])
> _Answer redacted_
@else
> {{ $connection['answer'] }}
@endif
@if ($connection['rating'])
>
> {{ str_repeat('★', $connection['rating']).str_repeat('☆', 5 - $connection['rating']) }}
@endif

@endforeach

Until next time — go build something.

Thanks,<br>
{{ $conferenceName }}
</x-mail::message>
