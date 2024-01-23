<?php

namespace App\Observers;

use App\Models\RentalEquipment;

class RentalEquipmentObserver
{
    /**
     * Handle the RentalEquipment "created" event.
     *
     * @param  \App\Models\RentalEquipment  $rentalEquipment
     * @return void
     */
    public function created(RentalEquipment $rentalEquipment)
    {
        createLogEvent(__FUNCTION__, $rentalEquipment);
    }

    /**
     * Handle the RentalEquipment "updated" event.
     *
     * @param  \App\Models\RentalEquipment  $rentalEquipment
     * @return void
     */
    public function updated(RentalEquipment $rentalEquipment)
    {
        createLogEvent(__FUNCTION__, $rentalEquipment);
    }

    /**
     * Handle the RentalEquipment "deleted" event.
     *
     * @param  \App\Models\RentalEquipment  $rentalEquipment
     * @return void
     */
    public function deleted(RentalEquipment $rentalEquipment)
    {
        createLogEvent(__FUNCTION__, $rentalEquipment);
    }

    /**
     * Handle the RentalEquipment "restored" event.
     *
     * @param  \App\Models\RentalEquipment  $rentalEquipment
     * @return void
     */
    public function restored(RentalEquipment $rentalEquipment)
    {
        //
    }

    /**
     * Handle the RentalEquipment "force deleted" event.
     *
     * @param  \App\Models\RentalEquipment  $rentalEquipment
     * @return void
     */
    public function forceDeleted(RentalEquipment $rentalEquipment)
    {
        //
    }
}
