<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Support\MagentoSourceItems;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class MagentoSourceItemsTest extends TestCase
{
    public function test_can_count_magento_categories()
    {
        Http::fake(function ($request) {
            return Http::response([
                'total_count' => 1,
            ], 200);
        });

        $sourceItems = new MagentoSourceItems();

        $count = $sourceItems->count();

        $this->assertEquals(1, $count);
    }

    public function test_can_update_product_quantity()
    {
        $product = MagentoProductFactory::new()->create();

        $response = [
            [
                'sku'         => $product->sku,
                'source_code' => 'default',
                'quantity'    => 250,
                'status'      => 1,
            ],
        ];

        (new MagentoSourceItems())->updateQuantities($response);

        $this->assertEquals(250, $product->fresh()->quantity);
        $this->assertEquals(1, $product->fresh()->is_in_stock);
    }

    public function test_can_run_on_missing_sku()
    {
        $response = [
            [
                'sku'         => 'foo',
                'source_code' => 'default',
                'quantity'    => 250,
                'status'      => 1,
            ],
        ];

        $source = (new MagentoSourceItems())->updateQuantities($response);

        $this->assertInstanceOf(MagentoSourceItems::class, $source);
    }
}
