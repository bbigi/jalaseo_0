<?php

namespace App\Observers;

use App\Models\Insight;

class InsightObserver
{
    /**
     * Handle the Insight "created" event.
     */
    public function created(Insight $insight): void
    {
        //
    }

    /**
     * Handle the Insight "updated" event.
     */
    public function updated(Insight $insight): void
    {
        //
    }

    /**
     * Handle the Insight "deleted" event.
     */
    public function deleted(Insight $insight): void
    {
        //
    }

    /**
     * Handle the Insight "restored" event.
     */
    public function restored(Insight $insight): void
    {
        //
    }

    /**
     * Handle the Insight "force deleted" event.
     */
    public function forceDeleted(Insight $insight): void
    {
        //
    }
}
