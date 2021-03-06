<?php

namespace Grayloon\MagentoStorage\Tests\Jobs;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductCategory;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Grayloon\MagentoStorage\Tests\TestCase;

class SyncMagentoProductCategoryTest extends TestCase
{
    public function test_can_sync_magento_product_category()
    {
        $category = MagentoCategoryFactory::new()->create();
        $product = MagentoProductFactory::new()->create();

        SyncMagentoProductCategory::dispatchNow($product->sku, $category->id, 1);

        $this->assertEquals(1, MagentoProductCategory::count());
        $this->assertEquals($product->id, MagentoProductCategory::first()->magento_product_id);
        $this->assertEquals($category->id, MagentoProductCategory::first()->magento_category_id);
        $this->assertEquals(1, MagentoProductCategory::first()->position);
    }

    public function test_doesnt_create_duplicates_of_magento_category()
    {
        $category = MagentoCategoryFactory::new()->create();
        $product = MagentoProductFactory::new()->create();
        MagentoProductCategoryFactory::new()->create([
            'magento_category_id' => $category->id,
            'magento_product_id'  => $product->id,
            'position'            => 2,
        ]);

        SyncMagentoProductCategory::dispatchNow($product->sku, $category->id, 1);

        $this->assertEquals(1, MagentoProductCategory::count());
        $this->assertEquals($product->id, MagentoProductCategory::first()->magento_product_id);
        $this->assertEquals($category->id, MagentoProductCategory::first()->magento_category_id);
        $this->assertEquals(1, MagentoProductCategory::first()->position);
    }

    public function test_product_categories_sync_missing_product_throws_exception()
    {
        $this->expectException('exception');
        $category = MagentoCategoryFactory::new()->create();

        SyncMagentoProductCategory::dispatchNow('foo', $category->id, 1);
    }

    /** @test */
    public function it_doesnt_sync_product_with_missing_category()
    {
        $product = MagentoProductFactory::new()->create();

        SyncMagentoProductCategory::dispatchNow($product->sku, 123, 1);

        $this->assertEquals(0, MagentoProductCategory::count());
    }
}
