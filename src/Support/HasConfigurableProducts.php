<?php

namespace Grayloon\MagentoStorage\Support;

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
                $configurableProduct->configurableProductOptions->forget($productOption);

                continue;
            }

            foreach ($productOption->optionValues as $valueKey => $optionValue) {
                $matchedProduct = $configurableProduct->configurableLinks
                    ->filter(function ($link) use ($optionValue, $productOption) {
                        return $link->customAttributeValue($productOption->attribute->name) == $optionValue->value;
                    });

                if ($matchedProduct->isEmpty()) {
                    $productOption->optionValues->forget($optionValue);
                    continue;
                } else {
                    // Attach relationship 
                    $configurableProduct->configurableProductOptions[$optionKey]->optionValues[$valueKey]->product = $matchedProduct->first();
                    $optionHasProduct = true;
                }
            }

            if (! $optionHasProduct) {
                $configurableProduct->configurableProductOptions->forget($productOption);
            }
        }


        return $configurableProduct;
    }
}
