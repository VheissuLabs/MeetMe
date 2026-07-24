<?php

namespace App\Events;

use App\Enums\Achievement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $userId, public Achievement $achievement) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AchievementUnlocked';
    }

    /** @return array<string, string> */
    public function broadcastWith(): array
    {
        return [
            'key' => $this->achievement->value,
            'name' => $this->achievement->label(),
            'description' => $this->achievement->description(),
            'icon' => $this->achievement->icon(),
        ];
    }
}
