<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOption;
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
        $option = MagentoConfigurableProductOptionFactory::new()->create();

        $option->load('product');

        $this->assertInstanceOf(MagentoProduct::class, $option->product);
    }

    public function test_belongs_to_attribute()
    {
        $option = MagentoConfigurableProductOptionFactory::new()->create();

        $option->load('attribute');

        $this->assertInstanceOf(MagentoCustomAttributeType::class, $option->attribute);
    }
}
