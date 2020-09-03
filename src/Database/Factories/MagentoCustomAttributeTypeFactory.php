<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;

$factory->define(MagentoCustomAttributeType::class, function (Faker $faker) {
    return [
        'name'         => $faker->catchPhrase,
        'display_name' => $faker->catchPhrase,
    ];
});
