<?php

namespace Grayloon\MagentoStorage\Console;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Events\MagentoProductSynced;
use Grayloon\MagentoStorage\Jobs\SyncMagentoProductsBatch;
use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Support\MagentoProducts;
use Illuminate\Console\Command;

class SyncMagentoProductsCommand extends Command
{
    protected $signature = 'magento:sync-products {--queue : Whether the job should be queued}';

    protected $description = 'Sync the products in Magento with your application.';

    protected $bar;
    protected $count;
    protected $pages;
    protected $pageSize = 100;

    public function handle()
    {
        $this->info('Retrieving products from Magento...');

        $this->count = (new MagentoProducts())->count();
        $this->info($this->count . ' products found.');

        $this->bar = $this->output
            ->createProgressBar($this->count);
        $this->pages = ceil(($this->count / $this->pageSize) + 1);

        return ($this->option('queue'))
            ? $this->processViaQueue()
            : $this->processViaCommand();
    }

    protected function processViaQueue()
    {
        for ($currentPage = 1; $this->pages > $currentPage; $currentPage++) {
            SyncMagentoProductsBatch::dispatch($this->pageSize, $currentPage);
        }

        $this->info('Queued job to sync Magento products.');
    }

    protected function processViaCommand()
    {
        if (config('magento.store_code')) {
            $this->newLine();
            $this->info('Default Store Code: "'. config('magento.store_code') .'" is configured in your env.');
            $this->info('We will only sync those products associated with "'. config('magento.store_code') .'".');
            $this->info('Existing non-associated products will be removed.');
        }

        $this->newLine();
        $this->bar->start();

        for ($currentPage = 1; $this->pages > $currentPage; $currentPage++) {
            $products = (new Magento())->api('products')
                ->all($this->pageSize, $currentPage)
                ->json();

            foreach ($products['items'] as $product) {
                try {
                    $apiProduct = (new Magento())->api('products')
                        ->show($product['sku'])
                        ->json();

                    if (in_array(config('magento.default_store_id'), ($apiProduct)['extension_attributes']['website_ids'])) {
                        $product = (new MagentoProducts())->updateOrCreateProduct($apiProduct);
            
                        event(new MagentoProductSynced($product));
                    } else {
                        // product doesnt exist in given website
                        return (new MagentoProducts())->deleteIfExists($this->sku);
                    }
                } catch (\Exception $e) {
                    //
                }

                $this->bar->advance();
            }
        }

        $this->bar->finish();
        $this->newLine();
        $this->newLine();
        $this->info(MagentoProduct::count() . ' products synced from the Magento instance to your application.');
    }
}
