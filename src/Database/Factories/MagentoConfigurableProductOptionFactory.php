<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoConfigurableProductOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoConfigurableProductOption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attribute_id'        => MagentoProductAttributeFactory::new()->create(),
            'magento_product_id'  => MagentoProductFactory::new()->create(),
            'label'               => $this->faker->catchPhrase,
            'position'            => 0,
        ];
    }
}
