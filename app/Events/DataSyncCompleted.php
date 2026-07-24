<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataSyncCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public string $message;
    public array $stats;

    public function __construct(string $type, string $message, array $stats = [])
    {
        $this->type = $type;
        $this->message = $message;
        $this->stats = $stats;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'DataSyncCompleted';
    }
}
