<?php

namespace App\Observers;

use App\Models\Residue;

class ResidueObserver
{
    /**
     * Handle the Residue "created" event.
     *
     * @param  \App\Models\Residue  $residue
     * @return void
     */
    public function created(Residue $residue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $residue);
    }

    /**
     * Handle the Residue "updated" event.
     *
     * @param  \App\Models\Residue  $residue
     * @return void
     */
    public function updated(Residue $residue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $residue);
    }

    /**
     * Handle the Residue "deleted" event.
     *
     * @param  \App\Models\Residue  $residue
     * @return void
     */
    public function deleted(Residue $residue)
    {
        createLogEvent(__FUNCTION__, __METHOD__, $residue);
    }

    /**
     * Handle the Residue "restored" event.
     *
     * @param  \App\Models\Residue  $residue
     * @return void
     */
    public function restored(Residue $residue)
    {
        //
    }

    /**
     * Handle the Residue "force deleted" event.
     *
     * @param  \App\Models\Residue  $residue
     * @return void
     */
    public function forceDeleted(Residue $residue)
    {
        //
    }
}
