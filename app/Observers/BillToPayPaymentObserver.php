<?php

namespace App\Observers;

use App\Models\BillToPayPayment;

class BillToPayPaymentObserver
{
    /**
     * Handle the BillToPayPayment "created" event.
     *
     * @param  \App\Models\BillToPayPayment  $billToPayPayment
     * @return void
     */
    public function created(BillToPayPayment $billToPayPayment)
    {
        createLogEvent(__FUNCTION__, $billToPayPayment);
    }

    /**
     * Handle the BillToPayPayment "updated" event.
     *
     * @param  \App\Models\BillToPayPayment  $billToPayPayment
     * @return void
     */
    public function updated(BillToPayPayment $billToPayPayment)
    {
        createLogEvent(__FUNCTION__, $billToPayPayment);
    }

    /**
     * Handle the BillToPayPayment "deleted" event.
     *
     * @param  \App\Models\BillToPayPayment  $billToPayPayment
     * @return void
     */
    public function deleted(BillToPayPayment $billToPayPayment)
    {
        createLogEvent(__FUNCTION__, $billToPayPayment);
    }

    /**
     * Handle the BillToPayPayment "restored" event.
     *
     * @param  \App\Models\BillToPayPayment  $billToPayPayment
     * @return void
     */
    public function restored(BillToPayPayment $billToPayPayment)
    {
        //
    }

    /**
     * Handle the BillToPayPayment "force deleted" event.
     *
     * @param  \App\Models\BillToPayPayment  $billToPayPayment
     * @return void
     */
    public function forceDeleted(BillToPayPayment $billToPayPayment)
    {
        //
    }
}
