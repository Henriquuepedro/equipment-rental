<?php

namespace App\Observers;

use App\Models\Rental;

class RentalObserver
{
    /**
     * Handle the Rental "created" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function created(Rental $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "updated" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function updated(Rental $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "deleted" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function deleted(Rental $rental)
    {
        createLogEvent(__FUNCTION__, $rental);
    }

    /**
     * Handle the Rental "restored" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function restored(Rental $rental)
    {
        //
    }

    /**
     * Handle the Rental "force deleted" event.
     *
     * @param  \App\Models\Rental  $rental
     * @return void
     */
    public function forceDeleted(Rental $rental)
    {
        //
    }
}
