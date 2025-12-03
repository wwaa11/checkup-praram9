<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegisterChannelEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $number;
    public $counter;
    public $language;

    public function __construct(string $number, string $counter, string $language = 'th')
    {
        $this->number   = $number;
        $this->counter  = $counter;
        $this->language = $language;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('register-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'register-number';
    }

    public function broadcastWith(): array
    {
        return [
            'number'    => $this->number,
            'counter'   => $this->counter,
            'language'  => $this->language,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
