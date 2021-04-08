<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProductsBatch;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class SyncMagentoProductsCommandTest extends TestCase
{
    public function test_magento_products_command_fires_product_job()
    {
        Queue::fake();
        Http::fake(function ($request) {
            return Http::response([
                'total_count' => 1,
            ], 200);
        });

        $this->artisan('magento:sync-products --queue');

        Queue::assertPushed(SyncMagentoProductsBatch::class);
    }
}
