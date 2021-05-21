<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerGroupFactory;
use Grayloon\MagentoStorage\Models\MagentoCustomerGroup;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SyncMagentoCustomerGroupsCommandTest extends TestCase
{
    /** @test */
    public function it_syncs_new_group()
    {
        Http::fake([
            '*/V1/customerGroups/search*' => Http::response([
                'items' => [
                    [
                        'id' => 1,
                        'code' => 'General',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Retail Customer',
                    ]
                ],
                'total_count' => 1,
            ], 200),
        ]);

        $this->artisan('magento:sync-customer-groups');

        $this->assertEquals(1, MagentoCustomerGroup::count());
    }

    /** @test */
    public function it_syncs_existing_group()
    {
        $group = MagentoCustomerGroupFactory::new()->create([
            'id' => 1,
            'code' => 'General',
            'tax_class_id' => 3,
            'tax_class_name' => 'Retail Customer',
        ]);

        Http::fake([
            '*/V1/customerGroups/search*' => Http::response([
                'items' => [
                    [
                        'id' => 1,
                        'code' => 'General - Change Me',
                        'tax_class_id' => 3,
                        'tax_class_name' => 'Retail Customer',
                    ]
                ],
                'total_count' => 1,
            ], 200),
        ]);

        $this->artisan('magento:sync-customer-groups');

        $this->assertEquals(1, MagentoCustomerGroup::count());
        $this->assertEquals('General - Change Me', $group->fresh()->code);
    }

    /** @test */
    public function it_handles_empty_response()
    {
        Http::fake([
            '*/V1/customerGroups/search*' => Http::response([
                'items' => [],
                'total_count' => 0,
            ], 200),
        ]);

        $this->artisan('magento:sync-customer-groups');

        $this->assertEquals(0, MagentoCustomerGroup::count());
    }
}
