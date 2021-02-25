<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeOptionFactory;
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
}
