<?php

namespace Grayloon\MagentoStorage\Support;

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
        $configurableProduct->load('configurableLinks', 'configurableLinks.customAttributes', 'configurableProductOptions', '')
    }
}