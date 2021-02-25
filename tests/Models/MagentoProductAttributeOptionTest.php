<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeOptionFactory;
use Grayloon\MagentoStorage\Models\MagentoProductAttribute;
use Grayloon\MagentoStorage\Models\MagentoProductAttributeOption;

class MagentoProductAttributeOptionTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertNotNull(MagentoProductAttributeOptionFactory::new()->create());
    }

    public function test_is_fillable()
    {
        $option = MagentoProductAttributeOption::create([
            'label'                        => 'hello',
            'value'                        => 'world',
            'magento_product_attribute_id' => $attributeId = MagentoProductAttributeFactory::new()->create()->id,
            'synced_at'                    => now(),
        ]);

        $this->assertEquals('hello', $option->label);
        $this->assertEquals('world', $option->value);
        $this->assertEquals($attributeId, $option->magento_product_attribute_id);
        $this->assertNotNull($option->synced_at);
    }

    public function test_belongs_to_attribute()
    {
        $attribute = MagentoProductAttributeFactory::new()->create([
            'id' => 10,
        ]);
        $option = MagentoProductAttributeOptionFactory::new()->create([
            'magento_product_attribute_id' => $attribute->id,
        ]);

        $option->load('attribute');

        $this->assertInstanceOf(MagentoProductAttribute::class, $option->attribute);
        $this->assertEquals(10, $option->attribute->id);
    }
}
