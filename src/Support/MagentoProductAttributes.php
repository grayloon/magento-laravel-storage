<?php

namespace Grayloon\MagentoStorage\Support;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoProductAttribute;

class MagentoProductAttributes extends PaginatableMagentoService
{
    /**
     * The amount of total product attributes.
     *
     * @return int
     * @throws \Exception
     */
    public function count()
    {
        $attributes = (new Magento())->api('productAttributes')
            ->all($this->pageSize, $this->currentPage);

        if (! $attributes->successful() || ! $attributes->json()['total_count']) {
            throw new Exception($attributes['message'] ?? 'An unknown error has occurred retrieving the Product Attribute count.');
        }

        return $attributes->json()['total_count'];
    }

    /**
     * Update or create the Magento Product Attribute attribute.
     *
     * @param  array  $apiAttribute
     * @throws \Exception
     * @return \Grayloon\MagentoStorage\Models\MagentoExtensionAttribute
     */
    public function updateOrCreate($apiAttribute)
    {
        if (! $apiAttribute['attribute_id']) {
            throw new Exception('Magento Product Attribute missing Attribute ID: '. json_encode($apiAttribute));
        }

        $attribute = MagentoProductAttribute::updateOrCreate(['id' => $apiAttribute['attribute_id']], [
            'name'          => $this->resolveAttributeLabel($apiAttribute['frontend_labels'], $apiAttribute['default_frontend_label']),
            'code'          => $apiAttribute['attribute_code'],
            'position'      => $apiAttribute['position'] ?? 0,
            'default_value' => $apiAttribute['default_value'],
            'type'          => $apiAttribute['frontend_input'],
            'synced_at'     => now(),
        ]);
        
        // $this->syncMagentoAttributeOptions($attribute, $apiAttribute['options]);

        return $attribute;
    }

    /**
     * Resolve the Product Attribute label by the associated assigned website.
     *
     * @param  array   $availableLabels
     * @param  string  $defaultLabel
     * @return string
     */
    public function resolveAttributeLabel($availableLabels, $defaultLabel)
    {
        if (! $availableLabels) {
            return $defaultLabel;
        }

        $labels = collect($availableLabels)
            ->when(config('magento.default_store_id'), function ($collection) {
                return $collection->filter(fn ($label) => $label['store_id'] == config('magento.default_store_id'));
            })
            ->when(! config('magento.default_store_id') && $defaultLabel, function ($collection) use ($defaultLabel) {
                return $collection->filter(fn ($label) => $label['label'] === $defaultLabel);
            });

        return $labels->first()['label'];
    }
}
