<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Grayloon\MagentoStorage\Models\MagentoCustomer;
use Grayloon\MagentoStorage\Support\HasCustomAttributes;
use Grayloon\MagentoStorage\Support\HasMagentoAuthentication;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class HasMagentoAuthenticationTest extends TestCase
{
    public function test_customerIsSignedIn_is_true()
    {
        $this->actingAs(factory(MagentoCustomer::class)->create());

        $this->assertTrue((new FakeHasMagentoAuthentication())->fakeCustomerIsSignedIn());
    }

    public function test_customerIsSignedIn_is_false()
    {
        $this->assertFalse((new FakeHasMagentoAuthentication())->fakeCustomerIsSignedIn());
    }
}

class FakeHasMagentoAuthentication
{
    use HasMagentoAuthentication;

    public function fakeCustomerIsSignedIn()
    {
        return $this->customerIsSignedIn();
    }
}
