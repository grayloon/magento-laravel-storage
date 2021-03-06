<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoCustomAttributeTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCustomAttributeType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'         => $this->faker->catchPhrase,
            'attribute_id' => $this->faker->randomNumber(),
            'display_name' => $this->faker->catchPhrase,
            'attribute_id' => rand(1, 100),
            'synced_at'    => now(),
        ];
    }
}
