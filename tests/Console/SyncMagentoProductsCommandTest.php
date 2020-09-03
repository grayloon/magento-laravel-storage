<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProducts;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class SyncMagentoProductsCommandTest extends TestCase
{
    public function test_magento_products_command_fires_product_job()
    {
        Queue::fake();

        $this->artisan('magento:sync-products');

        Queue::assertPushed(SyncMagentoProducts::class);
    }
}
