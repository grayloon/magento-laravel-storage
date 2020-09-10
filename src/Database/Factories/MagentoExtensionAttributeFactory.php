<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoExtensionAttributeTypeFactory;

class MagentoExtensionAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoExtensionAttribute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'magento_product_id'            => MagentoProductFactory::new()->create(),
            'magento_ext_attribute_type_id' => MagentoExtensionAttributeTypeFactory::new()->create(),
            'attribute'                     => [$this->faker->catchPhrase],
        ];
    }
}
