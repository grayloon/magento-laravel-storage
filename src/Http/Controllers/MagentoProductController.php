<?php

namespace Grayloon\MagentoStorage\Http\Controllers;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProductSingle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;

class MagentoProductController extends Controller
{
    /**
     * Launches a job to create or update the
     * specified SKU from the Magento API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $sku
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sku)
    {
        Bus::dispatch(new SyncMagentoProductSingle($sku));

        return response()->json(['success' => 'success'], 200);
    }
}
