<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $user;
    public string $status;

    public function __construct(array $user, string $status = 'online')
    {
        $this->user = $user;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('presence-global-users')
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserPresenceUpdated';
    }
}
