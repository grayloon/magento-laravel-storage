<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductLink;

$factory->define(MagentoProductLink::class, function (Faker $faker) {
    return [
        'product_id' => factory(MagentoProduct::class)->create(),
        'related_product_id' => factory(MagentoProduct::class)->create(),
        'link_type' => $faker->randomElement(['related', 'upsell']),
        'position' => 1,
    ];
});
