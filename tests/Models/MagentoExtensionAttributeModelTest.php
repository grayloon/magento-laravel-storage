<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Database\Factories\MagentoExtensionAttributeFactory;

class MagentoExtensionAttributeModelTest extends TestCase
{
    public function test_can_create_magento_ext_attribute()
    {
        $attribute = MagentoExtensionAttributeFactory::new()->create();

        $this->assertNotEmpty($attribute);
    }

    public function test_magento_ext_attribute_has_ext_attribute_type()
    {
        $attribute = MagentoExtensionAttributeFactory::new()->create();

        $type = $attribute->type()->first();

        $this->assertNotEmpty($type);
        $this->assertEquals($type->id, $attribute->magento_ext_attribute_type_id);
    }
}
