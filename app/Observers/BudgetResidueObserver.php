<?php

namespace App\Observers;

use App\Models\BudgetResidue;

class BudgetResidueObserver
{
    /**
     * Handle the BudgetResidue "created" event.
     *
     * @param  \App\Models\BudgetResidue  $budgetResidue
     * @return void
     */
    public function created(BudgetResidue $budgetResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budgetResidue);
    }

    /**
     * Handle the BudgetResidue "updated" event.
     *
     * @param  \App\Models\BudgetResidue  $budgetResidue
     * @return void
     */
    public function updated(BudgetResidue $budgetResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budgetResidue);
    }

    /**
     * Handle the BudgetResidue "deleted" event.
     *
     * @param  \App\Models\BudgetResidue  $budgetResidue
     * @return void
     */
    public function deleted(BudgetResidue $budgetResidue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $budgetResidue);
    }

    /**
     * Handle the BudgetResidue "restored" event.
     *
     * @param  \App\Models\BudgetResidue  $budgetResidue
     * @return void
     */
    public function restored(BudgetResidue $budgetResidue)
    {
        //
    }

    /**
     * Handle the BudgetResidue "force deleted" event.
     *
     * @param  \App\Models\BudgetResidue  $budgetResidue
     * @return void
     */
    public function forceDeleted(BudgetResidue $budgetResidue)
    {
        //
    }
}
