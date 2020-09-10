<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoCustomerAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCustomerAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id'           => rand(1, 10000),
            'customer_id'  => MagentoCustomerFactory::new()->create(),
            'region_code'  => $this->faker->stateAbbr,
            'region'       => $this->faker->state,
            'region_id'    => rand(1, 10000),
            'street'       => $this->faker->streetAddress,
            'telephone'    => $this->faker->phoneNumber,
            'postal_code'  => $this->faker->postcode,
            'city'         => $this->faker->city,
            'first_name'   => $this->faker->firstName,
            'last_name'    => $this->faker->lastName,
        ];
    }
}
