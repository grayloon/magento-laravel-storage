<?php

namespace Grayloon\MagentoStorage\Tests\Jobs;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductCategories;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SyncMagentoProductCategoriesTest extends TestCase
{
    public function test_can_sync_magento_product_categories_into_single_job()
    {
        config(['magento.store_code' => 'foo']);
        $category = MagentoCategoryFactory::new()->create();
        $product = MagentoProductFactory::new()->create();
        Http::fake([
            '*rest/foo/V1/categories/'.$category->id.'/products' => Http::response([
                [
                    'sku' => $product->sku,
                    'category_id' => $category->id,
                    'position' => 1,
                ],
            ], 200),
        ]);

        SyncMagentoProductCategories::dispatchNow($category->id);

        $this->assertEquals(1, MagentoProductCategory::count());
        $this->assertEquals($product->id, MagentoProductCategory::first()->magento_product_id);
        $this->assertEquals($category->id, MagentoProductCategory::first()->magento_category_id);
    }

    public function test_can_sync_magento_product_categories_handles_empty_response()
    {
        config(['magento.store_code' => 'foo']);
        $category = MagentoCategoryFactory::new()->create();
        Http::fake([
            '*rest/foo/V1/categories/'.$category->id.'/products' => Http::response([], 200),
        ]);

        SyncMagentoProductCategories::dispatchNow($category->id);

        $this->assertEquals(0, MagentoProductCategory::count());
    }
}
