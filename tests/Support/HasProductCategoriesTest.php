<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Grayloon\MagentoStorage\Support\HasProductCategories;
use Grayloon\MagentoStorage\Tests\TestCase;

class HasProductCategoriesTest extends TestCase
{
    public function test_creates_product_categories()
    {
        $product = factory(MagentoProduct::class)->create([
            'id' => 10,
        ]);
        $category = factory(MagentoCategory::class)->create([
            'id' => 20,
        ]);

        (new FakeSupportingProductCategoriesClass)->exposedSyncProductCategories([$category->id], $product);

        $this->assertEquals(1, MagentoProductCategory::count());
        $this->assertEquals(10, MagentoProductCategory::first()->magento_product_id);
        $this->assertEquals(20, MagentoProductCategory::first()->magento_category_id);
    }

    public function test_product_categories_can_receive_empty_result()
    {
        $product = factory(MagentoProduct::class)->create();
        (new FakeSupportingProductCategoriesClass)->exposedSyncProductCategories($categoryIds = [], $product);

        $this->assertEquals(0, MagentoProductCategory::count());
    }
}

class FakeSupportingProductCategoriesClass
{
    use HasProductCategories;

    public function exposedSyncProductCategories($categoryIds, $product)
    {
        return $this->syncProductCategories($categoryIds, $product);
    }
}
