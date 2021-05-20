<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerGroupFactory;
use Grayloon\MagentoStorage\Models\MagentoCustomerGroup;

class MagentoCustomerGroupModelTest extends TestCase
{
    /** @test */
    public function it_can_create()
    {
        $group = MagentoCustomerGroupFactory::new()->create();

        $this->assertNotEmpty($group);
        $this->assertInstanceOf(MagentoCustomerGroup::class, $group);
    }
}
