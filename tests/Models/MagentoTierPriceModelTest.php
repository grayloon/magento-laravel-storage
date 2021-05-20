<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoTierPriceFactory;
use Grayloon\MagentoStorage\Models\MagentoProduct;

class MagentoTierPriceModelTest extends TestCase
{
    /** @test */
    public function it_can_create()
    {
        $this->assertNotEmpty(MagentoTierPriceFactory::new()->create());
    }

    /** @test */
    public function magento_product_id_belongs_to_magento_product()
    {
        $priceTier = MagentoTierPriceFactory::new()->create();
        $priceTier->load('product');

        $this->assertInstanceOf(MagentoProduct::class, $priceTier->product);
    }
}
