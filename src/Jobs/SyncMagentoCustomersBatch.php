<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Support\MagentoCustomers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoCustomersBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pageSize;
    public $requestedPage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pageSize, $requestedPage)
    {
        $this->pageSize = $pageSize;
        $this->requestedPage = $requestedPage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customers = (new Magento())->api('customers')
            ->all($this->pageSize, $this->requestedPage)
            ->json();

        (new MagentoCustomers())->updateCustomers($customers['items']);
    }
}
