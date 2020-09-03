<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;

$factory->define(MagentoCustomAttribute::class, function (Faker $faker) {
    return [
        'attribute_type'      => $faker->catchPhrase,
        'attribute_type_id'   => factory(MagentoCustomAttributeType::class)->create(),
        'value'               => $faker->catchPhrase,
        'attributable_type'   => $faker->randomElement([MagentoProduct::class, MagentoCategory::class]),
        'attributable_id'     => fn (array $attribute) => factory($attribute['attributable_type']),
    ];
});
