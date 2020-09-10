<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

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
