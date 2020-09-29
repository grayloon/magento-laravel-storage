<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Database\Factories\MagentoCategoryFactory;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductCategories;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class SyncMagentoProductCategoriesCommandTest extends TestCase
{
    public function test_magento_product_categories_command_emits_job()
    {
        MagentoCategoryFactory::new()->create();
        Queue::fake();

        $this->artisan('magento:sync-product-categories');

        Queue::assertPushed(SyncMagentoProductCategories::class);
    }
    
    public function test_magento_product_categories_command_handles_empty_categories_result()
    {
        Queue::fake();

        $this->artisan('magento:sync-product-categories');

        Queue::assertNotPushed(SyncMagentoProductCategories::class);
    }
}
