<?php

namespace App\Observers;

use App\Models\RentalResidue;

class RentalResidueObserver
{
    /**
     * Handle the RentalResidue "created" event.
     *
     * @param  \App\Models\RentalResidue  $rentalResidue
     * @return void
     */
    public function created(RentalResidue $rentalResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalResidue);
    }

    /**
     * Handle the RentalResidue "updated" event.
     *
     * @param  \App\Models\RentalResidue  $rentalResidue
     * @return void
     */
    public function updated(RentalResidue $rentalResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalResidue);
    }

    /**
     * Handle the RentalResidue "deleted" event.
     *
     * @param  \App\Models\RentalResidue  $rentalResidue
     * @return void
     */
    public function deleted(RentalResidue $rentalResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $rentalResidue);
    }

    /**
     * Handle the RentalResidue "restored" event.
     *
     * @param  \App\Models\RentalResidue  $rentalResidue
     * @return void
     */
    public function restored(RentalResidue $rentalResidue)
    {
        //
    }

    /**
     * Handle the RentalResidue "force deleted" event.
     *
     * @param  \App\Models\RentalResidue  $rentalResidue
     * @return void
     */
    public function forceDeleted(RentalResidue $rentalResidue)
    {
        //
    }
}
