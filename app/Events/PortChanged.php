<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PortChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public array $port;

    public function __construct(string $action, array $port)
    {
        $this->action = $action;
        $this->port = $port;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('ports-channel')
        ];
    }

    public function broadcastAs(): string
    {
        return 'PortChanged';
    }
}
