<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WeatherUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $weatherData;
    public string $message;

    public function __construct(array $weatherData = [], string $message = 'Weather data updated successfully.')
    {
        $this->weatherData = $weatherData;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('weather-channel'),
            new Channel('system-sync')
        ];
    }

    public function broadcastAs(): string
    {
        return 'WeatherUpdated';
    }
}
