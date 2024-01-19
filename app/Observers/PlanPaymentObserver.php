<?php

namespace App\Observers;

use App\Models\PlanPayment;

class PlanPaymentObserver
{
    /**
     * Handle the PlanPayment "created" event.
     *
     * @param  \App\Models\PlanPayment  $planPayment
     * @return void
     */
    public function created(PlanPayment $planPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $planPayment);
    }

    /**
     * Handle the PlanPayment "updated" event.
     *
     * @param  \App\Models\PlanPayment  $planPayment
     * @return void
     */
    public function updated(PlanPayment $planPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $planPayment);
    }

    /**
     * Handle the PlanPayment "deleted" event.
     *
     * @param  \App\Models\PlanPayment  $planPayment
     * @return void
     */
    public function deleted(PlanPayment $planPayment)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $planPayment);
    }

    /**
     * Handle the PlanPayment "restored" event.
     *
     * @param  \App\Models\PlanPayment  $planPayment
     * @return void
     */
    public function restored(PlanPayment $planPayment)
    {
        //
    }

    /**
     * Handle the PlanPayment "force deleted" event.
     *
     * @param  \App\Models\PlanPayment  $planPayment
     * @return void
     */
    public function forceDeleted(PlanPayment $planPayment)
    {
        //
    }
}
