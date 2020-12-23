<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;

trait HasExtensionAttributes
{
    /**
     * Sync the Magento 2 Extension attributes with the associated model.
     *
     * @param  array  $attributes
     * @param  mixed  $product
     * @return void
     */
    protected function syncExtensionAttributes($attributes, $model)
    {
        foreach ($attributes as $key => $attribute) {
            $type = MagentoExtensionAttributeType::firstOrCreate(['type' => $key]);

            MagentoExtensionAttribute::updateOrCreate([
                'magento_product_id'            => $model->id,
                'magento_ext_attribute_type_id' => $type->id,
            ], ['attribute' => $attribute]);

            if ($type->type === 'category_links') {
                $this->resolveCategoryLinks($attribute, $model);
            }
        }

        return $this;
    }

    /**
     * Resolve the attributable links.
     *
     * @param  array $links
     * @return void
     */
    protected function resolveCategoryLinks($links, $product)
    {
        foreach ($links as $link) {
            MagentoProductCategory::updateOrCreate([
                'magento_product_id'  => $product->id,
                'magento_category_id' => MagentoCategory::find($link['category_id'])->id,
            ], ['position'            => $link['position']]);
        }
    }
}
