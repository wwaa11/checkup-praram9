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

    public $channel;
    public $number;
    public $counter;
    public $english;

    public function __construct(string $channel, string $number, string $counter, bool $english)
    {
        $this->channel = $channel;
        $this->number  = $number;
        $this->counter = $counter;
        $this->english = $english;
    }

    public function broadcastOn(): array
    {
        // Use $this->channel to access the property
        return [
            new Channel($this->channel . '-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->channel . '-number';
    }

    public function broadcastWith(): array
    {
        return [
            'number'        => $this->number,
            'counter'       => $this->counter,
            'preferEnglish' => $this->english,
            'timestamp'     => now()->toDateTimeString(),
        ];
    }
}
