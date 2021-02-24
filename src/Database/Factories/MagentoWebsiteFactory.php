<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoWebsite;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoWebsiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoWebsite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->company,
            'name' => $this->faker->company,
        ];
    }
}
