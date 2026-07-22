<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingAwaitingConfirmation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Meeting $meeting) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->meeting->recipient_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MeetingAwaitingConfirmation';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'initiator_name' => $this->meeting->initiator->name,
        ];
    }
}
