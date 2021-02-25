<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\MagentoStorage\Support\MagentoProductAttributes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductAttributes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $totalPages = ceil(((new MagentoProductAttributes())->count() / 50) + 1);

        for ($currentPage = 1; $totalPages > $currentPage; $currentPage++) {
            SyncMagentoProductAttributesBatch::dispatch(50, $currentPage);
        }
    }
}
