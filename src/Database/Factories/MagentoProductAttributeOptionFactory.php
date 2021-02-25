<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProductAttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoProductAttributeOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProductAttributeOption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label'                        => $this->faker->catchPhrase,
            'value'                        => $this->faker->catchPhrase,
            'magento_product_attribute_id' => MagentoProductAttributeFactory::new()->create(),
        ];
    }
}
