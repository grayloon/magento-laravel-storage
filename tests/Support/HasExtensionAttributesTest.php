<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Support\HasExtensionAttributes;
use Grayloon\MagentoStorage\Tests\TestCase;

class HasExtensionAttributesTest extends TestCase
{
    public function test_resolves_new_extension_attribute_type()
    {
        $product = factory(MagentoProduct::class)->create();

        (new FakeSupportingExtensionClass)->exposedSyncExtensionAttributes(['foo' => 'bar'], $product);

        $this->assertEquals(1, MagentoExtensionAttributeType::count());
        $this->assertEquals(1, MagentoExtensionAttribute::count());
        $this->assertEquals('foo', MagentoExtensionAttributeType::first()->type);
        $this->assertEquals('bar', MagentoExtensionAttribute::first()->attribute);
    }

    public function test_resolves_existing_extension_attribute_type()
    {
        $product = factory(MagentoProduct::class)->create();
        factory(MagentoExtensionAttributeType::class)->create([
            'type' => 'foo',
        ]);

        (new FakeSupportingExtensionClass)->exposedSyncExtensionAttributes(['foo' => 'bar'], $product);

        $this->assertEquals(1, MagentoExtensionAttributeType::count());
        $this->assertEquals(1, MagentoExtensionAttribute::count());
        $this->assertEquals('foo', MagentoExtensionAttributeType::first()->type);
        $this->assertEquals('bar', MagentoExtensionAttribute::first()->attribute);
    }
}

class FakeSupportingExtensionClass
{
    use HasExtensionAttributes;

    public function exposedSyncExtensionAttributes($attributes, $model)
    {
        return $this->syncExtensionAttributes($attributes, $model);
    }
}
