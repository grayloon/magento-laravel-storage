<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;
use Illuminate\Database\Eloquent\Factories\Factory;

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
