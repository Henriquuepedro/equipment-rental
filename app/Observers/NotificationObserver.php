<?php

namespace App\Observers;

use App\Models\Notification;

class NotificationObserver
{
    /**
     * Handle the Rental "created" event.
     *
     * @param  \App\Models\Notification  $rental
     * @return void
     */
    public function created(Notification $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "updated" event.
     *
     * @param  \App\Models\Notification  $rental
     * @return void
     */
    public function updated(Notification $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "deleted" event.
     *
     * @param  \App\Models\Notification  $rental
     * @return void
     */
    public function deleted(Notification $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "restored" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function restored(Notification $rental)
    {
        //
    }

    /**
     * Handle the Rental "force deleted" event.
     *
     * @param  \App\Models\Notification  $rental
     * @return void
     */
    public function forceDeleted(Notification $rental)
    {
        //
    }
}
