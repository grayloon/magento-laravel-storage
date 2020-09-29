<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sku;
    public $categoryId;
    public $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sku, $categoryId)
    {
        $this->sku = $sku;
        $this->categoryId = $categoryId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->product = MagentoProduct::where('sku', $this->sku)->firstOrFail();

        MagentoProductCategory::updateOrCreate([
            'magento_product_id' => $this->product->id,
            'magento_category_id' => $this->categoryId,
        ]);
    }
}
