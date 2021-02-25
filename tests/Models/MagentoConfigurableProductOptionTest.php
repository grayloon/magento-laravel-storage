<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductOptionFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductOption;

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
            'attribute_id'        => ($attribute = MagentoProductAttributeFactory::new()->create())->id,
            'magento_product_id'  => ($product = MagentoProductFactory::new()->create())->id,
            'label'               => 'foo',
            'position'            => 0,
        ]);

        $this->assertEquals(1, MagentoConfigurableProductOption::count());
        $this->assertEquals(500, $option->id);
        $this->assertEquals($attribute->id, $option->attribute_id);
        $this->assertEquals($product->id, $option->magento_product_id);
        $this->assertEquals('foo', $option->label);
        $this->assertEquals(0, $option->position);
    }
}
