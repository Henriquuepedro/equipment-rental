<?php

namespace App\Observers;

use App\Models\Company;
use App\Notifications\RegisterCompanyNotification;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     *
     * @param Company $company
     * @return void
     */
    public function created(Company $company): void
    {
        $company->notify(new RegisterCompanyNotification());

        createLogEvent(__FUNCTION__, __METHOD__, $company);
    }

    /**
     * Handle the Company "updated" event.
     *
     * @param Company $company
     * @return void
     */
    public function updated(Company $company): void
    {
        createLogEvent(__FUNCTION__, __METHOD__, $company);
    }

    /**
     * Handle the Company "deleted" event.
     *
     * @param Company $company
     * @return void
     */
    public function deleted(Company $company): void
    {
        createLogEvent(__FUNCTION__, __METHOD__, $company);
    }

    /**
     * Handle the Company "restored" event.
     *
     * @param Company $company
     * @return void
     */
    public function restored(Company $company)
    {
        //
    }

    /**
     * Handle the Company "force deleted" event.
     *
     * @param Company $company
     * @return void
     */
    public function forceDeleted(Company $company)
    {
        //
    }
}
