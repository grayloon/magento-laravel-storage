<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;

$factory->define(MagentoProductCategory::class, function () {
    return [
        'magento_product_id'  => factory(MagentoProduct::class)->create(),
        'magento_category_id' => factory(MagentoCategory::class)->create(),
    ];
});
