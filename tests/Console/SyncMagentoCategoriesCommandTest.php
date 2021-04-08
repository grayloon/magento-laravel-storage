<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Jobs\SyncMagentoCategoriesBatch;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class SyncMagentoCategoriesCommandTest extends TestCase
{
    public function test_magento_categories_command_fires_product_job()
    {
        Queue::fake();
        Http::fake(function ($request) {
            return Http::response([
                'total_count' => 1,
            ], 200);
        });
        
        $this->artisan('magento:sync-categories --queue');

        Queue::assertPushed(SyncMagentoCategoriesBatch::class);
    }
}
