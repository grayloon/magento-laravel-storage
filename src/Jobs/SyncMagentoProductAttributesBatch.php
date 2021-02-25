<?php

namespace Grayloon\MagentoStorage\Jobs;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Support\MagentoProductAttributes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductAttributesBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pageSize;
    public $requestedPage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pageSize, $requestedPage)
    {
        $this->pageSize = $pageSize;
        $this->requestedPage = $requestedPage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $attributes = (new Magento())->api('productAttributes')
            ->all($this->pageSize, $this->requestedPage);

        if (! $attributes->successful() || ! $attributes->json()['items']) {
            throw new Exception($attributes['message'] ?? 'An unknown error has occurred retrieving product attributes.');
        }

        foreach ($attributes->json()['items'] as $attribute) {
            (new MagentoProductAttributes())->updateOrCreate($attribute);
        }
    }
}
