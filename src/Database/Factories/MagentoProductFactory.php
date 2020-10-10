<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku'        => $this->faker->ean8,
            'name'       => $this->faker->bs,
            'price'      => $this->faker->randomFloat(2, 1, 500),
            'status'     => 1,
            'visibility' => 1,
            'type'       => 'simple',
            'slug'       => $this->faker->slug,
        ];
    }
}
