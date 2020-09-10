<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;

class MagentoCustomAttributeModelTest extends TestCase
{
    public function test_can_create_magento_custom_attribute()
    {
        $attribute = MagentoCustomAttributeFactory::new()->create();

        $this->assertNotEmpty($attribute);
    }

    public function test_can_create_magento_custom_attribute_poly_type_can_be_product()
    {
        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoProduct::class,
            'attributable_id'     => MagentoProductFactory::new()->create(),
        ]);

        $this->assertNotEmpty($attribute);
        $this->assertEquals(MagentoProduct::class, $attribute->attributable_type);
    }

    public function test_can_create_magento_custom_attribute_poly_type_can_be_category()
    {
        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCategory::class,
            'attributable_id'     => MagentoCategoryFactory::new()->create(),
        ]);

        $this->assertNotEmpty($attribute);
        $this->assertEquals(MagentoCategory::class, $attribute->attributable_type);
    }

    public function test_custom_attribute_type_id_belongs_to_attribute_type_relationship()
    {
        $type = MagentoCustomAttributeTypeFactory::new()->create();
        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attribute_type' => $type->id,
        ]);

        $query = MagentoCustomAttribute::with('type')->first();

        $this->assertNotEmpty($attribute);
        $this->assertEquals($attribute->attribute_type, $type->id);
        $this->assertInstanceOf(MagentoCustomAttributeType::class, $query->type()->first());
    }
}
