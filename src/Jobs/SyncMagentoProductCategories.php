<?php

namespace Grayloon\MagentoStorage\Jobs;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoProductCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMagentoProductCategories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $categoryId;
    public $links;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->links = (new Magento())->api('categories')
            ->products($this->categoryId)
            ->json();
        
        MagentoProductCategory::where('magento_category_id', $this->categoryId)->delete();

        foreach ($this->links as $link) {
            SyncMagentoProductCategory::dispatch($link['sku'], $this->categoryId, $link['position']);
        }
    }
}
