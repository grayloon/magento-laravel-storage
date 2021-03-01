<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoConfigurableProductLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoConfigurableProductLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoConfigurableProductLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'configurable_product_id' => MagentoProductFactory::new()->create(),
            'product_id' => MagentoProductFactory::new()->create(),
            'synced_at' => now(),
        ];
    }
}
