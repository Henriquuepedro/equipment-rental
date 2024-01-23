<?php

namespace App\Observers;

use App\Models\BudgetEquipment;

class BudgetEquipmentObserver
{
    /**
     * Handle the BudgetEquipment "created" event.
     *
     * @param  \App\Models\BudgetEquipment  $budgetEquipment
     * @return void
     */
    public function created(BudgetEquipment $budgetEquipment)
    {
        createLogEvent(__FUNCTION__, $budgetEquipment);
    }

    /**
     * Handle the BudgetEquipment "updated" event.
     *
     * @param  \App\Models\BudgetEquipment  $budgetEquipment
     * @return void
     */
    public function updated(BudgetEquipment $budgetEquipment)
    {
        createLogEvent(__FUNCTION__, $budgetEquipment);
    }

    /**
     * Handle the BudgetEquipment "deleted" event.
     *
     * @param  \App\Models\BudgetEquipment  $budgetEquipment
     * @return void
     */
    public function deleted(BudgetEquipment $budgetEquipment)
    {
        createLogEvent(__FUNCTION__, $budgetEquipment);
    }

    /**
     * Handle the BudgetEquipment "restored" event.
     *
     * @param  \App\Models\BudgetEquipment  $budgetEquipment
     * @return void
     */
    public function restored(BudgetEquipment $budgetEquipment)
    {
        //
    }

    /**
     * Handle the BudgetEquipment "force deleted" event.
     *
     * @param  \App\Models\BudgetEquipment  $budgetEquipment
     * @return void
     */
    public function forceDeleted(BudgetEquipment $budgetEquipment)
    {
        //
    }
}
