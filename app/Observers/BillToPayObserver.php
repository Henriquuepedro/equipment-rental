<?php

namespace App\Observers;

use App\Models\BillToPay;

class BillToPayObserver
{
    /**
     * Handle the BillToPay "created" event.
     *
     * @param  \App\Models\BillToPay  $billToPay
     * @return void
     */
    public function created(BillToPay $billToPay)
    {
        createLogEvent(__FUNCTION__, $billToPay);
    }

    /**
     * Handle the BillToPay "updated" event.
     *
     * @param  \App\Models\BillToPay  $billToPay
     * @return void
     */
    public function updated(BillToPay $billToPay)
    {
        createLogEvent(__FUNCTION__, $billToPay);
    }

    /**
     * Handle the BillToPay "deleted" event.
     *
     * @param  \App\Models\BillToPay  $billToPay
     * @return void
     */
    public function deleted(BillToPay $billToPay)
    {
        createLogEvent(__FUNCTION__, $billToPay);
    }

    /**
     * Handle the BillToPay "restored" event.
     *
     * @param  \App\Models\BillToPay  $billToPay
     * @return void
     */
    public function restored(BillToPay $billToPay)
    {
        //
    }

    /**
     * Handle the BillToPay "force deleted" event.
     *
     * @param  \App\Models\BillToPay  $billToPay
     * @return void
     */
    public function forceDeleted(BillToPay $billToPay)
    {
        //
    }
}
