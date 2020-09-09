<?php

namespace Grayloon\MagentoStorage\Support;

use Illuminate\Support\Facades\Auth;
use Grayloon\MagentoStorage\Models\MagentoCustomer;

trait HasMagentoAuthentication
{
    /**
     * Determine if the authenticated user is a customer.
     *
     * @return bool
     */
    protected function customerIsSignedIn()
    {
        return (Auth::user() instanceof MagentoCustomer);
    }
}
