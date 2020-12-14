<?php

namespace Grayloon\MagentoStorage\Tests\Console;

use Exception;
use Grayloon\MagentoStorage\Database\Factories\MagentoCustomAttributeTypeFactory;
use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class SyncMagentoCustomAttributeTypesCommandTest extends TestCase
{
    public function test_launches_jobs_to_import_types()
    {
        Queue::fake();
        
        $type = MagentoCustomAttributeTypeFactory::new()->create();

        $this->artisan('magento:sync-custom-attribute-types');

        Queue::assertPushed(UpdateProductAttributeGroup::class);
    }

    public function test_throws_exception_when_no_types_are_available()
    {
        $this->expectException(Exception::class);
        
        Queue::fake();

        $this->artisan('magento:sync-custom-attribute-types');

        Queue::assertNothingPushed();
    }
}
