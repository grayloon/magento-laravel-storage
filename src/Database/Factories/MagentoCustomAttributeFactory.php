<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;

class MagentoCustomAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCustomAttribute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attribute_type'      => $this->faker->catchPhrase,
            'attribute_type_id'   => MagentoCustomAttributeTypeFactory::new()->create(),
            'value'               => $this->faker->catchPhrase,
            'attributable_type'   => $this->faker->randomElement([MagentoProduct::class, MagentoCategory::class]),
            'attributable_id'     => 1,
        ];
    }
}
