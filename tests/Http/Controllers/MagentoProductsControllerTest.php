<?php

namespace Grayloon\MagentoStorage\Tests\Http\Controllers;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProductSingle;
use Grayloon\MagentoStorage\Tests\TestCase;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Queue;

class MagentoProductsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([Authorize::class, VerifyCsrfToken::class]);
    }

    public function test_can_successfully_send_request_to_update_product_over_api()
    {
        Queue::fake();

        $this->getJson(route('laravel-magento-api.products.update', 'foo'))
            ->assertSuccessful();

        Queue::assertPushed(SyncMagentoProductSingle::class);
    }
}
