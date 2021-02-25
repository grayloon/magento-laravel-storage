<?php

namespace Grayloon\MagentoStorage\Support;

use Exception;
use Grayloon\Magento\Magento;

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
}