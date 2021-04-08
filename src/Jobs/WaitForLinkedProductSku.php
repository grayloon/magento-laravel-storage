<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Support\MagentoProducts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForLinkedProductSku implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Magento Product.
     *
     * @var \Grayloon\Magento\Models\MagentoProduct
     */
    public $product;

    /**
     * The Magento API Response.
     *
     * @var array
     */
    public $response = [];

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param  \Grayloon\Magento\Models\MagentoProduct  $product
     * @return void
     */
    public function __construct(MagentoProduct $product, $response)
    {
        $this->product = $product;
        $this->response = $response;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $checkSku = MagentoProduct::where('sku', $this->response['linked_product_sku'])->first();

        if (! $checkSku) {
            throw new ModelNotFoundException("Failed to find a find product {$this->response['linked_product_sku']} to link with product {$this->product->sku}");
        }

        (new MagentoProducts())->updateProductLink($this->response, $this->product);
    }
}
