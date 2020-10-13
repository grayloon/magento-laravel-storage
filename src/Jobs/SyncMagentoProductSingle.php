<?php

namespace Grayloon\MagentoStorage\Jobs;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Events\MagentoProductSynced;
use Grayloon\MagentoStorage\Support\MagentoProducts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductSingle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Magento Product SKU for the API.
     *
     * @var string
     */
    public $sku;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sku)
    {
        $this->sku = $sku;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $apiProduct = (new Magento())->api('products')
            ->show($this->sku);

        if (! $apiProduct->ok()) {
            throw new Exception('Error fetching SKU: '. $this->sku .' Error: '. $apiProduct->json()['body'] ?? 'N/A');
        }

        $product = (new MagentoProducts())->updateOrCreateProduct($apiProduct->json());

        event(new MagentoProductSynced($product));
    }
}
