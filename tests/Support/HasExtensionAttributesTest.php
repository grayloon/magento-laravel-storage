<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoExtensionAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttribute;
use Grayloon\MagentoStorage\Models\MagentoExtensionAttributeType;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Grayloon\MagentoStorage\Support\HasExtensionAttributes;
use Grayloon\MagentoStorage\Tests\TestCase;

class HasExtensionAttributesTest extends TestCase
{
    public function test_resolves_new_extension_attribute_type()
    {
        $product = MagentoProductFactory::new()->create();

        (new FakeSupportingExtensionClass)->exposedSyncExtensionAttributes(['foo' => 'bar'], $product);

        $this->assertEquals(1, MagentoExtensionAttributeType::count());
        $this->assertEquals(1, MagentoExtensionAttribute::count());
        $this->assertEquals('foo', MagentoExtensionAttributeType::first()->type);
        $this->assertEquals('bar', MagentoExtensionAttribute::first()->attribute);
    }

    public function test_resolves_existing_extension_attribute_type()
    {
        $product = MagentoProductFactory::new()->create();
        MagentoExtensionAttributeTypeFactory::new()->create([
            'type' => 'foo',
        ]);

        (new FakeSupportingExtensionClass)->exposedSyncExtensionAttributes(['foo' => 'bar'], $product);

        $this->assertEquals(1, MagentoExtensionAttributeType::count());
        $this->assertEquals(1, MagentoExtensionAttribute::count());
        $this->assertEquals('foo', MagentoExtensionAttributeType::first()->type);
        $this->assertEquals('bar', MagentoExtensionAttribute::first()->attribute);
    }

    public function test_resolves_extension_attributes_category_types()
    {
        $product = MagentoProductFactory::new()->create();
        $category = MagentoCategoryFactory::new()->create();

        (new FakeSupportingExtensionClass)->exposedSyncExtensionAttributes(['category_links' => [
            [
                'category_id' => $category->id,
                'position'    => 1,
            ]
        ]], $product);

        $this->assertEquals(1, MagentoExtensionAttributeType::count());
        $this->assertEquals(1, MagentoExtensionAttribute::count());
        $this->assertEquals(1, MagentoProductCategory::count());
        $this->assertEquals(1, MagentoProductCategory::first()->position);
        $this->assertEquals($category->id, MagentoProductCategory::first()->magento_category_id);
        $this->assertEquals($product->id, MagentoProductCategory::first()->magento_product_id);
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
