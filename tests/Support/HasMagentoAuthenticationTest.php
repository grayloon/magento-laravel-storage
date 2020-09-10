<?php

namespace Grayloon\MagentoStorage\Tests\Support;

use Grayloon\MagentoStorage\Database\Factories\MagentoCustomerFactory;
use Grayloon\MagentoStorage\Support\HasMagentoAuthentication;
use Grayloon\MagentoStorage\Tests\TestCase;

class HasMagentoAuthenticationTest extends TestCase
{
    public function test_customer_is_signed_in_is_true()
    {
        $this->actingAs(MagentoCustomerFactory::new()->create());

        $this->assertTrue((new FakeHasMagentoAuthentication())->fakeCustomerIsSignedIn());
    }

    public function test_customer_is_signed_in_is_false()
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
