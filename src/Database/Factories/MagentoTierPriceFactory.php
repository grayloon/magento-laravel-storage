<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoTierPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoTierPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoTierPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'magento_product_id'   => MagentoProductFactory::new(),
            'customer_group_id'    => MagentoCustomerGroupFactory::new(),
            'value'                => $this->faker->randomFloat(2, 1, 500),
            'quantity'             => $this->faker->numberBetween(1, 100),
            'extension_attributes' => [],
        ];
    }
}
