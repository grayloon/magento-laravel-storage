<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Models\MagentoConfigurableProductLink;
use Grayloon\MagentoStorage\Models\MagentoProduct;

trait HasConfigurableProducts
{
    /**
     * The options with their associated attribute types.
     *
     * @param \Grayloon\MagentoStorage\Models\MagentoProduct
     * @return \Illuminate\Support\Collection
     */
    protected function resolveConfigurableOptions($configurableProduct)
    {
        $configurableProduct->load(
            'configurableLinks',
            'configurableLinks.customAttributes',
            'configurableProductOptions',
            'configurableProductOptions.attribute',
            'configurableProductOptions.optionValues'
        );

        if ($configurableProduct->configurableLinks->isEmpty() || $configurableProduct->configurableProductOptions->isEmpty()) {
            return $configurableProduct;
        }

        foreach ($configurableProduct->configurableProductOptions as $optionKey => $productOption) {
            $optionHasProduct = false;
            if ($productOption->optionValues->isEmpty()) {
                $configurableProduct->configurableProductOptions->forget($optionKey);

                continue;
            }

            foreach ($productOption->optionValues as $valueKey => $optionValue) {
                $matchedProduct = $configurableProduct->configurableLinks
                    ->filter(function ($link) use ($optionValue, $productOption) {
                        return $link->customAttributeValue($productOption->attribute->name) == $optionValue->value;
                    });

                if ($matchedProduct->isEmpty()) {
                    $configurableProduct->configurableProductOptions->where('id', $productOption->id)
                        ->first()
                        ->optionValues
                        ->forget($valueKey);

                    continue;
                } else {
                    // Attach relationship
                    $configurableProduct->configurableProductOptions[$optionKey]->optionValues[$valueKey]->product = $matchedProduct->first();
                    $optionHasProduct = true;
                }
            }

            if (! $optionHasProduct) {
                $configurableProduct->configurableProductOptions->forget($optionKey);
            }
        }

        return $configurableProduct;
    }

    /**
     * Determine if a product belongs to a configurable product.
     *
     * @param \Grayloon\MagentoStorage\Models\MagentoProduct  $product
     * @return bool
     */
    protected function productBelongsToConfigurableProduct($product)
    {
        return MagentoConfigurableProductLink::where('product_id', $product->id)->exists();
    }

    /**
     * Retrieve the configurable product to the provided product.
     *
     * @param \Grayloon\MagentoStorage\Models\MagentoProduct  $product
     * @return \Grayloon\MagentoStorage\Models\MagentoProduct
     */
    protected function getConfigurableProductParent($product)
    {
        return MagentoProduct::whereHas('configurableLinks', fn ($query) => $query->where('product_id', $product->id))->first();
    }
}
