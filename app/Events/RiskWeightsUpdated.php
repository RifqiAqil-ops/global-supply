<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiskWeightsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $weights;
    public string $message;

    public function __construct(array $weights, string $message = 'Risk weights successfully updated by Admin.')
    {
        $this->weights = $weights;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('risk-weights')
        ];
    }

    public function broadcastAs(): string
    {
        return 'RiskWeightsUpdated';
    }
}
