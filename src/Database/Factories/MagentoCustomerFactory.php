<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoCustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCustomer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id'                 => rand(1, 10000),
            'group_id'           => rand(1, 10000),
            'email'              => $this->faker->safeEmail,
            'first_name'         => $this->faker->firstName,
            'last_name'          => $this->faker->lastName,
            'store_id'           => 1,
            'website_id'         => 1,
        ];
    }
}
