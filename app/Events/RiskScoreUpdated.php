<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiskScoreUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $riskData;
    public string $message;

    public function __construct(array $riskData = [], string $message = 'Country risk score recalculated successfully.')
    {
        $this->riskData = $riskData;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('risk-channel'),
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'RiskScoreUpdated';
    }
}
