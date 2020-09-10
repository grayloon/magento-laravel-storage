<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Grayloon\MagentoStorage\Models\MagentoProductMedia;

class MagentoProductMediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProductMedia::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id'          => rand(1, 10000),
            'product_id'  => MagentoProductFactory::new()->create(),
            'media_type'  => 'image',
            'position'    => 1,
            'disabled'    => false,
            'file'        => '/f/foo.jpg',
        ];
    }
}
