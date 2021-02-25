<?php

namespace Grayloon\MagentoStorage\Tests\Jobs;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProductAttributes;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductAttributesBatch;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class SyncMagentoProductAttributesTest extends TestCase
{
    public function test_can_work_job()
    {
        Queue::fake([
            SyncMagentoProductAttributesBatch::class
        ]);
        
        Http::fake(fn () => Http::response(['total_count' => 1], 200));

        SyncMagentoProductAttributes::dispatchNow(1, 1);

        Queue::assertPushed(SyncMagentoProductAttributesBatch::class);
    }
}
