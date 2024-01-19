<?php

namespace App\Observers;

use App\Models\PlanHistory;

class PlanHistoryObserver
{
    /**
     * Handle the PlanHistory "created" event.
     *
     * @param  \App\Models\PlanHistory  $planHistory
     * @return void
     */
    public function created(PlanHistory $planHistory)
    {
        createLogEvent(__FUNCTION__, $planHistory);
    }

    /**
     * Handle the PlanHistory "updated" event.
     *
     * @param  \App\Models\PlanHistory  $planHistory
     * @return void
     */
    public function updated(PlanHistory $planHistory)
    {
        createLogEvent(__FUNCTION__, $planHistory);
    }

    /**
     * Handle the PlanHistory "deleted" event.
     *
     * @param  \App\Models\PlanHistory  $planHistory
     * @return void
     */
    public function deleted(PlanHistory $planHistory)
    {
        createLogEvent(__FUNCTION__, $planHistory);
    }

    /**
     * Handle the PlanHistory "restored" event.
     *
     * @param  \App\Models\PlanHistory  $planHistory
     * @return void
     */
    public function restored(PlanHistory $planHistory)
    {
        //
    }

    /**
     * Handle the PlanHistory "force deleted" event.
     *
     * @param  \App\Models\PlanHistory  $planHistory
     * @return void
     */
    public function forceDeleted(PlanHistory $planHistory)
    {
        //
    }
}
