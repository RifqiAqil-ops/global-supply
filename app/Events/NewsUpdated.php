<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $newsData;
    public string $message;

    public function __construct(array $newsData = [], string $message = 'News feed updated successfully.')
    {
        $this->newsData = $newsData;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('news-channel'),
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewsUpdated';
    }
}
