<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;

class MagentoExtensionAttributeTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoExtensionAttributeType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => $this->faker->bs,
        ];
    }
}
