<?php

namespace Grayloon\MagentoStorage\Tests;

use Grayloon\MagentoStorage\Database\Factories\MagentoWebsiteFactory;
use Grayloon\MagentoStorage\Models\MagentoWebsite;

class MagentoWebsiteTest extends TestCase
{
    public function test_can_create()
    {
        $this->assertNotNull(MagentoWebsiteFactory::new()->create());
    }

    public function test_is_fillable()
    {
        $website = MagentoWebsite::create([
            'id'   => 10,
            'code' => 'Hello',
            'name' => 'World',
        ]);

        $this->assertEquals(10, $website->id);
        $this->assertEquals('Hello', $website->code);
        $this->assertEquals('World', $website->name);
    }
}
