<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CountryUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $countryData;
    public string $message;

    public function __construct(array $countryData = [], string $message = 'Country master data updated.')
    {
        $this->countryData = $countryData;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('country-channel'),
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'CountryUpdated';
    }
}
