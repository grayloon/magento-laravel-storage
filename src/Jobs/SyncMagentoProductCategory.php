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

    /**
     * The Magento Product Sku.
     *
     * @var string
     */
    public $sku;

    /**
     * The Magento Product Category ID.
     *
     * @var int
     */
    public $categoryId;

    /**
     * The Magento Product.
     *
     * @var \Grayloon\MagentoStorage\Models\MagentoProduct
     */
    public $product;

    /**
     * The position ranking between the category and product.
     *
     * @var int
     */
    public $position;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sku, $categoryId, $position)
    {
        $this->sku = $sku;
        $this->categoryId = $categoryId;
        $this->position = $position;
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
            'magento_product_id'  => $this->product->id,
            'magento_category_id' => $this->categoryId,
        ], ['position'            => $this->position]);
    }
}
