<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Illuminate\Support\Str;

class MagentoProducts extends PaginatableMagentoService
{
    use HasCustomAttributes, HasExtensionAttributes, HasProductLinks, HasMediaEntries;

    /**
     * The amount of total products.
     *
     * @return int
     */
    public function count()
    {
        $products = (new Magento())->api('products')
            ->all($this->pageSize, $this->currentPage)
            ->json();

        return $products['total_count'];
    }

    /**
     * Updates a product from the Magento API.
     *
     * @param  array  $apiProduct
     * @return Grayloon\Magento\Models\MagentoProduct
     */
    public function updateOrCreateProduct($apiProduct)
    {
        $product = MagentoProduct::updateOrCreate(['id' => $apiProduct['id']], [
            'id'          => $apiProduct['id'],
            'name'        => $apiProduct['name'],
            'sku'         => $apiProduct['sku'],
            'price'       => $apiProduct['price'] ?? 0.00,
            'quantity'    => $apiProduct['extension_attributes']['stock_item']['qty'] ?? 0,
            'is_in_stock' => $apiProduct['extension_attributes']['stock_item']['is_in_stock'] ?? false,
            'status'      => $apiProduct['status'],
            'visibility'  => $apiProduct['visibility'],
            'type'        => $apiProduct['type_id'],
            'created_at'  => $apiProduct['created_at'],
            'updated_at'  => $apiProduct['updated_at'],
            'weight'      => $apiProduct['weight'] ?? 0,
            'synced_at'   => now(),
        ]);

        $this->syncExtensionAttributes($apiProduct['extension_attributes'], $product);
        $this->syncCustomAttributes($apiProduct['custom_attributes'], $product, true);
        $this->syncProductLinks($apiProduct['product_links'], $product);
        $this->downloadProductImages($apiProduct['media_gallery_entries'] ?? [], $product);
        $this->syncStockItemAsAttributes($apiProduct['extension_attributes']['stock_item'] ?? [], $product);

        return $product;
    }

    /**
     * Store Stock Item data as custom attributes.
     *
     * @param  array  $stockItems
     * @param  \Grayloon\Magento\Models\MagentoProduct  $product
     * @return void
     */
    protected function syncStockItemAsAttributes($stockItems, $product)
    {
        if (! $stockItems) {
            return;
        }

        foreach ($stockItems as $key => $stockItem) {
            $type = MagentoCustomAttributeType::firstOrCreate(['name' => 'stock-item--'.$key], [
                'display_name' => Str::title(Str::snake(Str::studly($key), ' ')),
                'options'      => [],
                'synced_at'    => now(),
            ]);

            $product
                ->customAttributes()
                ->updateOrCreate(['attribute_type_id' => $type->id], [
                    'attribute_type' => 'stock-item--'.$key,
                    'value'          => is_array($stockItem) ? json_encode($stockItem) : $stockItem,
                    'synced_at'      => now(),
                ]);
        }
    }

    /**
     * Deletes the record if the product no longer exists.
     *
     * @param  string  $sku
     * @return void
     */
    public function deleteIfExists($sku)
    {
        $product = MagentoProduct::where('sku', $sku)->first();

        if ($product) {
            $product->delete();
        }

        return $this;
    }

    /**
     * Check if Custom Attributes have applied rules to be applied.
     *
     * @param  array  $attribute
     * @param  \Grayloon\Magento\Models\MagentoProduct $product
     * @return void
     */
    protected function applyConditionalRules($attribute, $product)
    {
        if ($attribute['attribute_code'] === 'url_key') {
            $product->update([
                'slug' => $attribute['value'],
            ]);
        }

        return $this;
    }
}
