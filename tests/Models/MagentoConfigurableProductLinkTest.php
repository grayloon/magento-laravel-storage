<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoConfigurableProductLinkFactory;
use Grayloon\MagentoStorage\Models\MagentoConfigurableProductLink;
use Illuminate\Support\Carbon;

class MagentoConfigurableProductLinkTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertInstanceOf(MagentoConfigurableProductLink::class, MagentoConfigurableProductLinkFactory::new()->create());
    }

    public function test_is_casted()
    {
        $this->assertInstanceOf(Carbon::class, MagentoConfigurableProductLinkFactory::new()->create()->synced_at);
    }
}
