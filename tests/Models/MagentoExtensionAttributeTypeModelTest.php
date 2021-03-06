<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoExtensionAttributeTypeFactory;

class MagentoExtensionAttributeTypeModelTest extends TestCase
{
    public function test_can_create_magento_ext_attribute_type()
    {
        $type = MagentoExtensionAttributeTypeFactory::new()->create();

        $this->assertNotEmpty($type);
    }
}
