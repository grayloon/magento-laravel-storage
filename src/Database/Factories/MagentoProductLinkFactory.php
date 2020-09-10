<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProductLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoProductLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProductLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => MagentoProductFactory::new()->create(),
            'related_product_id' => MagentoProductFactory::new()->create(),
            'link_type' => $this->faker->randomElement(['related', 'upsell']),
            'position' => 1,
        ];
    }
}
