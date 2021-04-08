<?php

namespace Grayloon\MagentoStorage\Console;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Jobs\SyncMagentoCategoriesBatch;
use Grayloon\MagentoStorage\Models\MagentoCategory;
use Grayloon\MagentoStorage\Support\MagentoCategories;
use Illuminate\Console\Command;

class SyncMagentoCategoriesCommand extends Command
{
    protected $signature = 'magento:sync-categories {--queue : Whether the job should be queued}';

    protected $description = 'Sync the categories in Magento with your application.';

    protected $bar;
    protected $count;
    protected $pages;
    protected $pageSize = 50;

    public function handle()
    {
        $this->info('Retrieving categories from Magento...');

        $this->count = (new MagentoCategories())->count();
        $this->info($this->count . ' categories found.');

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
            SyncMagentoCategoriesBatch::dispatch($this->pageSize, $currentPage);
        }

        $this->info('Queued job to sync Magento categories.');
    }

    protected function processViaCommand()
    {
        if (env('MAGENTO_DEFAULT_CATEGORY')) {
            $this->newLine();
            $this->info('Looks like we have a root category of "'. env('MAGENTO_DEFAULT_CATEGORY') .'" configured in your env.');
            $this->info('We will only sync those categories associated with ID "'. env('MAGENTO_DEFAULT_CATEGORY') .'".');
            $this->info('Existing non-associated categories will be removed.');
        }

        $this->newLine();
        $this->bar->start();

        for ($currentPage = 1; $this->pages > $currentPage; $currentPage++) {
            $categories = (new Magento())->api('categories')
                ->all($this->pageSize, $currentPage)
                ->json();

            foreach ($categories['items'] as $category) {
                try {
                    (new MagentoCategories())->updateCategory($category);
                } catch (\Exception $e) {
                    //
                }
                $this->bar->advance();
            }
        }

        $this->bar->finish();
        $this->newLine();
        $this->newLine();
        $this->info(MagentoCategory::count() . ' categories synced from the Magento instance to your application.');
    }
}
