<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoProductAttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProductAttribute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'     => $this->faker->catchPhrase,
            'code'     => $this->faker->slug,
            'position' => $this->faker->randomNumber(),
            'type'     => 'select',
        ];
    }
}
