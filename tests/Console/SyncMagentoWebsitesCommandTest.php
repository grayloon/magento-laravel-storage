<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Database\Factories\MagentoWebsiteFactory;
use Grayloon\MagentoStorage\Models\MagentoWebsite;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SyncMagentoWebsitesCommandTest extends TestCase
{
    public function test_can_create_website()
    {
        Http::fake([
            '*rest/all/V1/store/websites*' => Http::response([
                [
                    'id'   => 0,
                    'code' => 'admin',
                    'name' => 'admin',
                ],
            ], 200),
        ]);

        $this->artisan('magento:sync-websites');

        $this->assertEquals(1, MagentoWebsite::count());
        $this->assertEquals('admin', MagentoWebsite::first()->name);
    }

    public function test_can_update_website()
    {
        $website = MagentoWebsiteFactory::new()->create();
        Http::fake([
            '*rest/all/V1/store/websites*' => Http::response([
                [
                    'id'   => $website->id,
                    'code' => $website->code,
                    'name' => 'Change This',
                ],
            ], 200),
        ]);

        $this->artisan('magento:sync-websites');

        $this->assertEquals(1, MagentoWebsite::count());
        $this->assertEquals('Change This', MagentoWebsite::first()->name);
    }
}
