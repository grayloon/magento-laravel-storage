<?php

namespace Grayloon\MagentoStorage\Console;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProductCategories;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Illuminate\Console\Command;

class SyncMagentoProductCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync-product-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs all of the category relationships that belong to the product.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $categories = MagentoCategory::get();

        foreach ($categories as $category) {
            SyncMagentoProductCategories::dispatch($category->id);
            $this->info('Successfully launched job to get the products assigned to the "'. $category->name .'" category.');
        }
    }
}
