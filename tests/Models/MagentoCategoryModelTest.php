<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoProduct;

class MagentoCategoryModelTest extends TestCase
{
    public function test_can_create_magento_category()
    {
        $category = MagentoCategoryFactory::new()->create();

        $this->assertNotEmpty($category);
        $this->assertInstanceOf(MagentoCategory::class, $category);
    }

    public function test_magento_category_can_have_parent_category()
    {
        $category = MagentoCategoryFactory::new()->create([
            'parent_id' => $parent = MagentoCategoryFactory::new()->create(),
        ]);

        $this->assertNotEmpty($category, $parent);
        $this->assertEquals($category->parent()->first()->id, $parent->id);
        $this->assertNotEquals($category->parent()->first()->id, $category->id);
    }

    public function test_can_add_custom_attributes_to_magento_category()
    {
        $category = MagentoCategoryFactory::new()->create();

        $attribute = $category->customAttributes()->updateOrCreate([
            'attribute_type'    => 'foo',
            'attribute_type_id' => MagentoCustomAttributeTypeFactory::new()->create(),
            'value'             => 'bar',
        ]);

        $this->assertNotEmpty($attribute);
        $this->assertEquals('foo', $attribute->attribute_type);
        $this->assertEquals('bar', $attribute->value);
        $this->assertEquals(MagentoCategory::class, $attribute->attributable_type);
        $this->assertEquals($category->id, $attribute->attributable_id);
    }

    public function test_magento_category_can_get_single_product()
    {
        MagentoCategoryFactory::new()->create(); // create non-assigned category.
        MagentoProductFactory::new()->create(); // create non-assigned category.

        $category = MagentoCategoryFactory::new()->create();
        MagentoProductCategoryFactory::new()->create([
            'magento_category_id' => $category->id,
        ]);

        $products = $category->products()->get();
        $this->assertNotEmpty($products);
        $this->assertEquals(1, $products->count());
        $this->assertInstanceOf(MagentoProduct::class, $products->first());
    }

    public function test_custom_attribute_value_helper_returns_value_of_custom_attribute()
    {
        $category = MagentoCategoryFactory::new()->create();

        MagentoCustomAttributeFactory::new()->create([
            'attributable_type'   => MagentoCategory::class,
            'attributable_id'     => $category->id,
            'attribute_type'      => 'foo',
            'value'               => 'bar',
        ]);

        $category = $category->with('customAttributes')->first();

        $this->assertEquals(1, $category->customAttributes()->count());
        $this->assertEquals('bar', $category->customAttributeValue('foo'));
    }

    public function test_custom_attribute_value_helper_returns_null_of_invalid_custom_attribute()
    {
        $category = MagentoCategoryFactory::new()->create();

        $category = $category->with('customAttributes')->first();

        $this->assertEquals(0, $category->customAttributes()->count());
        $this->assertNull($category->customAttributeValue('foo'));
    }
}
