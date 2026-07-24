<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WatchlistUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $action;
    public array $watchlist;

    public function __construct(int $userId, string $action, array $watchlist)
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->watchlist = $watchlist;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'WatchlistUpdated';
    }
}
