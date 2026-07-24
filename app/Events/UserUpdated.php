<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public array $userData;

    public function __construct(string $action, array $userData)
    {
        $this->action = $action;
        $this->userData = $userData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-channel')
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserUpdated';
    }
}
