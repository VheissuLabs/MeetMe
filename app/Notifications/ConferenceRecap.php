<?php

namespace App\Notifications;

use App\Actions\BuildConnections;
use App\Actions\GetLeaderboard;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConferenceRecap extends Notification implements ShouldQueue
{
    use Queueable;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $connections = app(BuildConnections::class)->for($notifiable);
        $position = app(GetLeaderboard::class)->positions()[$notifiable->id] ?? null;
        $conferenceName = Event::current()->name;

        return (new MailMessage)
            ->subject(__(':conference — your recap', ['conference' => $conferenceName]))
            ->markdown('mail.conference-recap', [
                'user' => $notifiable,
                'conferenceName' => $conferenceName,
                'connections' => $connections,
                'total' => count($connections),
                'position' => $position,
                'averageRating' => $notifiable->averageAnswerRating(),
            ]);
    }
}
