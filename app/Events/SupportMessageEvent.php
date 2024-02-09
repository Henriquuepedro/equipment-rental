<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SupportMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $master;
    public int $company_user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public int $support_id,
        public int $company_id,
        public int $support_message_id,
        public bool $mark_close
    )
    {
        $this->master       = hasAdminMaster();
        $this->company_user = Auth::user()->__get('company_id');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return ['update-support-message-notification'];
    }
}
