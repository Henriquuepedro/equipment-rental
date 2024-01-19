<?php

namespace App\Observers;

use App\Models\EquipmentWallet;

class EquipmentWalletObserver
{
    /**
     * Handle the EquipmentWallet "created" event.
     *
     * @param  \App\Models\EquipmentWallet  $equipmentWallet
     * @return void
     */
    public function created(EquipmentWallet $equipmentWallet)
    {
        createLogEvent(__FUNCTION__, $equipmentWallet);
    }

    /**
     * Handle the EquipmentWallet "updated" event.
     *
     * @param  \App\Models\EquipmentWallet  $equipmentWallet
     * @return void
     */
    public function updated(EquipmentWallet $equipmentWallet)
    {
        createLogEvent(__FUNCTION__, $equipmentWallet);
    }

    /**
     * Handle the EquipmentWallet "deleted" event.
     *
     * @param  \App\Models\EquipmentWallet  $equipmentWallet
     * @return void
     */
    public function deleted(EquipmentWallet $equipmentWallet)
    {
        createLogEvent(__FUNCTION__, $equipmentWallet);
    }

    /**
     * Handle the EquipmentWallet "restored" event.
     *
     * @param  \App\Models\EquipmentWallet  $equipmentWallet
     * @return void
     */
    public function restored(EquipmentWallet $equipmentWallet)
    {
        //
    }

    /**
     * Handle the EquipmentWallet "force deleted" event.
     *
     * @param  \App\Models\EquipmentWallet  $equipmentWallet
     * @return void
     */
    public function forceDeleted(EquipmentWallet $equipmentWallet)
    {
        //
    }
}
