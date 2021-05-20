<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoTierPriceFactory;

class MagentoTierPriceModelTest extends TestCase
{
    /** @test */
    public function it_can_create()
    {
        $this->assertNotEmpty(MagentoTierPriceFactory::new()->create());
    }
}
