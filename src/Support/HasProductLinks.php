<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Jobs\WaitForLinkedProductSku;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductLink;

trait HasProductLinks
{
    /**
     * Sync the Magento 2 Related Links with the Product.
     *
     * @param  array  $attributes
     * @param  \Grayloon\Magento\Models\MagentoProduct  $product
     * @return void
     */
    protected function syncProductLinks($links, $product)
    {
        foreach ($links as $link) {
            $this->updateProductLink($link, $product);
        }

        return $this;
    }

    /**
     * Creates or updates a product link based on the related value.
     *
     * @param  array  $link
     * @param \Grayloon\Magento\Models\MagentoProduct  $product
     * @return void
     */
    public function updateProductLink($link, $product)
    {
        $productLink = MagentoProduct::where('sku', $link['linked_product_sku'])->first();

        if (! $productLink) {
            return WaitForLinkedProductSku::dispatch($product, $link);
        }

        MagentoProductLink::updateOrCreate([
            'product_id'         => $product->id,
            'related_product_id' => $productLink->id,
        ], [
            'link_type'   => $link['link_type'],
            'position'    => $link['position'],
            'synced_at'   => now(),
        ]);
    }
}
