<?php
namespace Grayloon\MagentoStorage\Database\Factories;

use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoConfigurableProductOptionValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MagentoConfigurableProductOptionValue::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'magento_configurable_product_option_id' => MagentoConfigurableProductOptionFactory::new()->create()->id,
            'value' => $this->faker->name,
            'synced_at' => now(),
        ];
    }
}
