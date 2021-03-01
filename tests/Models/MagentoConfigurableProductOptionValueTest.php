<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionValueFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOptionValue;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Illuminate\Support\Carbon;

class MagentoConfigurableProductOptionValueTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertNotNull(MagentoConfigurableProductOptionValueFactory::new()->create());
    }

    public function test_is_fillable()
    {
        $id =  MagentoConfigurableProductOptionFactory::new()->create()->id;
        $value = MagentoConfigurableProductOptionValue::create([
            'magento_configurable_product_option_id' => $id,
            'value' => 'foo',
        ]);

        $this->assertEquals($id, $value->magento_configurable_product_option_id);
        $this->assertEquals('foo', $value->value);
    }

    public function test_is_casted()
    {
        $option = MagentoConfigurableProductOptionValueFactory::new()->create();

        $this->assertInstanceOf(Carbon::class, $option->synced_at);
    }

    public function test_has_one_custom_attribute_type()
    {
        $customType = MagentoCustomAttributeTypeFactory::new()->create([
            'attribute_id' => 400,
        ]);

        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'id' => 125,
            'attribute_type_id' => 400,
        ]);

        $optionValue = MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => 125,
        ]);

        $type = $optionValue->customAttributeType()->first();

        $this->assertInstanceOf(MagentoCustomAttributeType::class, $type);
        $this->assertEquals($customType->attribute_id, $type->attribute_id);
    }

    public function test_resolves_value_returns_original_value_when_missing_type()
    {
        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'id' => 125,
            'attribute_type_id' => 400,
        ]);

        $optionValue = MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
            'value' => 'foo',
        ]);

        $this->assertEquals('foo', $optionValue->value);
    }

    public function test_resolves_value_returns_the_type_option_value()
    {
        MagentoCustomAttributeTypeFactory::new()->create([
            'attribute_id' => 400,
            'options' => [
                [
                    'value' => '200',
                    'label' => 'foo',
                ]
            ]
        ]);
        MagentoConfigurableProductOptionFactory::new()->create([
            'id' => 125,
            'attribute_type_id' => 400,
        ]);
        $optionValue = MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => 125,
            'value' => 200,
        ]);

        $this->assertEquals('foo', $optionValue->value);
    }

    public function test_resolves_value_returns_original_value_when_empty_options_array()
    {
        MagentoCustomAttributeTypeFactory::new()->create([
            'attribute_id' => 400,
            'options' => [],
        ]);
        MagentoConfigurableProductOptionFactory::new()->create([
            'id' => 125,
            'attribute_type_id' => 400,
        ]);
        $optionValue = MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => 125,
            'value' => 200,
        ]);

        $this->assertEquals(200, $optionValue->value);
    }
}
