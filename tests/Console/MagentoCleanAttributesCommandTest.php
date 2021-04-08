<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeFactory;
use Grayloon\MagentoStorage\Database\Factories\MagentoProductFactory;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Tests\TestCase;

class MagentoCleanAttributesCommandTest extends TestCase
{
    public function test_it_removes_missing_relational_custom_attributes()
    {
        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attributable_type' => MagentoProduct::class,
            'attributable_id'   => 123,
        ]);

        $this->artisan('magento:clean');

        $this->assertDeleted($attribute);
    }

    public function test_it_keeps_relational_match_custom_attribute()
    {
        $product = MagentoProductFactory::new()->create();
        $attribute = MagentoCustomAttributeFactory::new()->create([
            'attributable_type' => MagentoProduct::class,
            'attributable_id'   => $product->id,
        ]);

        $this->artisan('magento:clean');

        $this->assertEquals(1, MagentoCustomAttribute::count());
    }
}
