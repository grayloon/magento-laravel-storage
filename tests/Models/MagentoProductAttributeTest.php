<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductAttributeOptionFactory;
use Grayloon\MagentoStorage\Models\MagentoProductAttribute;
use Grayloon\MagentoStorage\Models\MagentoProductAttributeOption;

class MagentoProductAttributeTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertNotNull(MagentoProductAttributeFactory::new()->create());
    }

    public function test_is_fillable()
    {
        $attribute = MagentoProductAttribute::create([
            'id'            => 10,
            'name'          => 'foo',
            'code'          => 'bar',
            'position'      => 100,
            'default_value' => 'hello',
            'type'          => 'world',
            'synced_at'     => now(),
        ]);

        $this->assertEquals(10, $attribute->id);
        $this->assertEquals('foo', $attribute->name);
        $this->assertEquals('bar', $attribute->code);
        $this->assertEquals(100, $attribute->position);
        $this->assertEquals('hello', $attribute->default_value);
        $this->assertEquals('world', $attribute->type);
        $this->assertNotNull($attribute->synced_at);
    }

    public function test_has_many_options()
    {
        $attribute = MagentoProductAttributeFactory::new()->create([
            'id' => 10,
        ]);

        $options = MagentoProductAttributeOptionFactory::new()->count(5)->create([
            'magento_product_attribute_id' => $attribute->id,
        ]);

        $attribute->load('options');

        $this->assertEquals(5, $attribute->options->count());
    }
}
