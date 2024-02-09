<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SupportEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $master;
    public int $company_user;
    public int $user_message_sent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public bool $new_support,
        public int $company_id
    )
    {
        $this->master               = hasAdminMaster();
        $this->company_user         = Auth::user()->__get('company_id');
        $this->user_message_sent    = Auth::user()->__get('id');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return ['update-support-notification'];
    }
}
