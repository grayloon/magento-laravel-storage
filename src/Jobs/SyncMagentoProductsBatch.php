<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\Magento\Magento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductsBatch implements ShouldQueue
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
        $products = (new Magento())->api('products')
            ->all($this->pageSize, $this->requestedPage)
            ->json();

        foreach ($products['items'] as $product) {
            SyncMagentoProductSingle::dispatch($product['sku']);
        }
    }
}
