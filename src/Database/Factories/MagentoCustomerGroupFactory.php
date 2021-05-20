<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoCustomerGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCustomerGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->company,
            'tax_class_id' => $this->faker->randomNumber(),
            'tax_class_name' => $this->faker->catchPhrase,
        ];
    }
}
