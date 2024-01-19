<?php

namespace App\Observers;

use App\Models\BudgetPayment;

class BudgetPaymentObserver
{
    /**
     * Handle the BudgetPayment "created" event.
     *
     * @param  \App\Models\BudgetPayment  $budgetPayment
     * @return void
     */
    public function created(BudgetPayment $budgetPayment)
    {
        createLogEvent(__FUNCTION__, $budgetPayment);
    }

    /**
     * Handle the BudgetPayment "updated" event.
     *
     * @param  \App\Models\BudgetPayment  $budgetPayment
     * @return void
     */
    public function updated(BudgetPayment $budgetPayment)
    {
        createLogEvent(__FUNCTION__, $budgetPayment);
    }

    /**
     * Handle the BudgetPayment "deleted" event.
     *
     * @param  \App\Models\BudgetPayment  $budgetPayment
     * @return void
     */
    public function deleted(BudgetPayment $budgetPayment)
    {
        createLogEvent(__FUNCTION__, $budgetPayment);
    }

    /**
     * Handle the BudgetPayment "restored" event.
     *
     * @param  \App\Models\BudgetPayment  $budgetPayment
     * @return void
     */
    public function restored(BudgetPayment $budgetPayment)
    {
        //
    }

    /**
     * Handle the BudgetPayment "force deleted" event.
     *
     * @param  \App\Models\BudgetPayment  $budgetPayment
     * @return void
     */
    public function forceDeleted(BudgetPayment $budgetPayment)
    {
        //
    }
}
