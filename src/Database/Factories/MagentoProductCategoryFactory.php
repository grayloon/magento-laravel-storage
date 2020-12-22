<?php

namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoProductCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'magento_product_id'  => MagentoProductFactory::new()->create(),
            'magento_category_id' => MagentoCategoryFactory::new()->create(),
            'position'            => 1,
        ];
    }
}
