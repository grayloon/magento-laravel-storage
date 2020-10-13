<?php

namespace Grayloon\MagentoStorage\Tests\Jobs;

use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Events\MagentoProductSynced;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductSingle;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class SyncMagentoProductSingleTest extends TestCase
{
    public function test_sync_product_single_can_sync()
    {
        Http::fake([
            '*rest/all/V1/products/DMPC001' => Http::response($this->fakeProductResponse(), 200),
        ]);

        SyncMagentoProductSingle::dispatchNow('DMPC001');

        $this->assertEquals(1, MagentoProduct::count());
    }

    public function test_deletes_if_record_not_found()
    {
        $product = MagentoProductFactory::new()->create();
        Http::fake([
            '*rest/all/V1/products/'.$product->sku => Http::response(['message' => 'Product not found.'], 401),
        ]);

        SyncMagentoProductSingle::dispatchNow($product->sku);

        $this->assertEquals(0, MagentoProduct::count());
    }

    public function test_sync_product_single_fires_synced_event()
    {
        Event::fake();
        Http::fake([
            '*rest/all/V1/products/DMPC001' => Http::response($this->fakeProductResponse(), 200),
        ]);

        SyncMagentoProductSingle::dispatchNow('DMPC001');

        $this->assertEquals(1, MagentoProduct::count());
        Event::assertDispatched(MagentoProductSynced::class);
        Event::assertDispatched(fn (MagentoProductSynced $event) => $event->product->sku === 'DMPC001');
    }

    protected function fakeProductResponse($attributes = null)
    {
        $product = [
            'id'         => '1',
            'name'       => 'Dunder Mifflin Paper',
            'sku'        => 'DMPC001',
            'price'      => 19.99,
            'status'     => '1',
            'visibility' => '1',
            'type_id'    => 'simple',
            'created_at' => now(),
            'updated_at' => now(),
            'weight'     => 10.00,
            'extension_attributes' => [
                'website_id' => [1],
                'stock_item' => [
                    'item_id' => 1,
                    'product_id' => 1,
                    'stock_id' => 1,
                    'qty' => 250,
                    'is_in_stock' => true,
                    'is_qty_decimal' => false,
                    'show_default_notification_message' => false,
                    'use_config_min_qty' => true,
                    'min_qty' => 3,
                    'use_config_min_sale_qty' => 1,
                    'min_sale_qty' => 1,
                    'use_config_max_sale_qty' => true,
                    'max_sale_qty' => 10000,
                    'use_config_backorders' => true,
                    'backorders' => 0,
                    'use_config_notify_stock_qty' => true,
                    'notify_stock_qty' => 0,
                    'use_config_qty_increments' => true,
                    'qty_increments' => 0,
                    'use_config_enable_qty_inc' => true,
                    'enable_qty_increments' => false,
                    'use_config_manage_stock' => false,
                    'manage_stock' => false,
                    'low_stock_date' => null,
                    'is_decimal_divided' => false,
                    'stock_status_changed_auto' => 0,
                ],
            ],
            'product_links' => [],
            'media_gallery_entries' => [],
            'custom_attributes' => [],
        ];

        if ($attributes) {
            $product = array_merge($product, $attributes);
        }

        return $product;
    }
}
