<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionValueFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOption;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOptionValue;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;

class MagentoConfigurableProductOptionTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertNotNull(MagentoConfigurableProductOptionFactory::new()->create());
    }

    public function test_is_fillable()
    {
        $option = MagentoConfigurableProductOption::create([
            'id'                  => 500,
            'attribute_type_id'   => ($type = MagentoCustomAttributeTypeFactory::new()->create())->attribute_id,
            'magento_product_id'  => ($product = MagentoProductFactory::new()->create())->id,
            'label'               => 'foo',
            'position'            => 0,
        ]);

        $this->assertEquals(1, MagentoConfigurableProductOption::count());
        $this->assertEquals(500, $option->id);
        $this->assertEquals($type->attribute_id, $option->attribute_type_id);
        $this->assertEquals($product->id, $option->magento_product_id);
        $this->assertEquals('foo', $option->label);
        $this->assertEquals(0, $option->position);
    }

    public function test_belongs_to_product()
    {
        $product = MagentoProductFactory::new()->create([
            'id' => 123,
        ]);
        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'magento_product_id' => $product->id,
        ]);

        $option->load('product');

        $this->assertInstanceOf(MagentoProduct::class, $option->product);
        $this->assertEquals(123, $option->magento_product_id);
    }

    public function test_belongs_to_attribute()
    {
        $option = MagentoConfigurableProductOptionFactory::new()->create();

        $option->load('attribute');

        $this->assertInstanceOf(MagentoCustomAttributeType::class, $option->attribute);
    }

    public function test_has_many_option_values()
    {
        $option = MagentoConfigurableProductOptionFactory::new()->create([
            'id' => 123,
        ]);
        MagentoConfigurableProductOptionValueFactory::new()->create([
            'magento_configurable_product_option_id' => $option->id,
        ]);

        $option->load('optionValues');

        $this->assertInstanceOf(MagentoConfigurableProductOptionValue::class, $option->optionValues->first());
        $this->assertEquals(123, $option->optionValues->first()->magento_configurable_product_option_id);
    }
}
