<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArticlePublished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public array $article;

    public function __construct(string $action, array $article)
    {
        $this->action = $action;
        $this->article = $article;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('articles-channel')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ArticlePublished';
    }
}
