<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Models\MagentoCustomer;
use Illuminate\Support\Facades\Auth;

trait HasMagentoAuthentication
{
    /**
     * Determine if the authenticated user is a customer.
     *
     * @return bool
     */
    protected function customerIsSignedIn()
    {
        return Auth::user() instanceof MagentoCustomer;
    }
}
