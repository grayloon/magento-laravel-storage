<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'            => $this->faker->catchPhrase,
            'is_active'       => true,
            'position'        => random_int(1, 100),
            'level'           => random_int(1, 100),
            'path'            => '1/1',
            'include_in_menu' => true,
            'slug'            => $this->faker->slug,
        ];
    }
}
