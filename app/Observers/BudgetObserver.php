<?php

namespace App\Observers;

use App\Models\Budget;

class BudgetObserver
{
    /**
     * Handle the Budget "created" event.
     *
     * @param  \App\Models\Budget  $budget
     * @return void
     */
    public function created(Budget $budget)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budget);
    }

    /**
     * Handle the Budget "updated" event.
     *
     * @param  \App\Models\Budget  $budget
     * @return void
     */
    public function updated(Budget $budget)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budget);
    }

    /**
     * Handle the Budget "deleted" event.
     *
     * @param  \App\Models\Budget  $budget
     * @return void
     */
    public function deleted(Budget $budget)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budget);
    }

    /**
     * Handle the Budget "restored" event.
     *
     * @param  \App\Models\Budget  $budget
     * @return void
     */
    public function restored(Budget $budget)
    {
        //
    }

    /**
     * Handle the Budget "force deleted" event.
     *
     * @param  \App\Models\Budget  $budget
     * @return void
     */
    public function forceDeleted(Budget $budget)
    {
        //
    }
}
