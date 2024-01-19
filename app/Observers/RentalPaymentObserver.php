<?php

namespace App\Observers;

use App\Models\RentalPayment;

class RentalPaymentObserver
{
    /**
     * Handle the RentalPayment "created" event.
     *
     * @param  \App\Models\RentalPayment  $rentalPayment
     * @return void
     */
    public function created(RentalPayment $rentalPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalPayment);
    }

    /**
     * Handle the RentalPayment "updated" event.
     *
     * @param  \App\Models\RentalPayment  $rentalPayment
     * @return void
     */
    public function updated(RentalPayment $rentalPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalPayment);
    }

    /**
     * Handle the RentalPayment "deleted" event.
     *
     * @param  \App\Models\RentalPayment  $rentalPayment
     * @return void
     */
    public function deleted(RentalPayment $rentalPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalPayment);
    }

    /**
     * Handle the RentalPayment "restored" event.
     *
     * @param  \App\Models\RentalPayment  $rentalPayment
     * @return void
     */
    public function restored(RentalPayment $rentalPayment)
    {
        //
    }

    /**
     * Handle the RentalPayment "force deleted" event.
     *
     * @param  \App\Models\RentalPayment  $rentalPayment
     * @return void
     */
    public function forceDeleted(RentalPayment $rentalPayment)
    {
        //
    }
}
