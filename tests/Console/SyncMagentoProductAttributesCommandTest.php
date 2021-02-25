<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Models\MagentoProductAttribute;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SyncMagentoProductAttributesCommandTest extends TestCase
{
    public function test_magento_categories_command_fires_product_job()
    {
        Http::fake(fn () => Http::response(['total_count' => 1,
            'items' => [
                [
                    'attribute_id'    => 300,
                    'frontend_labels' => [
                        [
                            'store_id' => 1,
                            'label'    => 'foo',
                        ],
                        [
                            'store_id' => 2,
                            'label'    => 'bar',
                        ],
                    ],
                    'default_frontend_label' => 'foo',
                    'attribute_code'         => 'test_attribute',
                    'position'               => 4,
                    'default_value'          => '',
                    'frontend_input'         => 'select',
                    'options'                => [],
                ],
            ],
        ], 200));

        $this->artisan('magento:sync-product-attributes');

        $this->assertEquals(1, MagentoProductAttribute::count());
    }
}
