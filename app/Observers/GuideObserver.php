<?php

namespace App\Observers;

use App\Models\Guide;

class GuideObserver
{
    /**
     * Handle the Guide "created" event.
     *
     * @param Guide $guide
     * @return void
     */
    public function created(Guide $guide)
    {
        createLogEvent(__FUNCTION__, $guide);
    }

    /**
     * Handle the Guide "updated" event.
     *
     * @param Guide $guide
     * @return void
     */
    public function updated(Guide $guide)
    {
        createLogEvent(__FUNCTION__, $guide);
    }

    /**
     * Handle the Guide "deleted" event.
     *
     * @param Guide $guide
     * @return void
     */
    public function deleted(Guide $guide)
    {
        createLogEvent(__FUNCTION__, $guide);
    }

    /**
     * Handle the Guide "restored" event.
     *
     * @param Guide $guide
     * @return void
     */
    public function restored(Guide $guide)
    {
        //
    }

    /**
     * Handle the Guide "force deleted" event.
     *
     * @param Guide $guide
     * @return void
     */
    public function forceDeleted(Guide $guide)
    {
        //
    }
}
