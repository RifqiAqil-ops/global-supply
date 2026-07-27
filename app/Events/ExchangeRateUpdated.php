<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExchangeRateUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $exchangeData;
    public string $message;

    public function __construct(array $exchangeData = [], string $message = 'Exchange rates updated successfully.')
    {
        $this->exchangeData = $exchangeData;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('currency-channel'),
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ExchangeRateUpdated';
    }
}
