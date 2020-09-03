<?php

namespace Grayloon\MagentoStorage\Console;

use Grayloon\MagentoStorage\Jobs\SyncMagentoProducts;
use Illuminate\Console\Command;

class SyncMagentoProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the product data from the Magneto 2 API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        SyncMagentoProducts::dispatch();

        $this->info('Successfully launched job to import magento products.');
    }
}
