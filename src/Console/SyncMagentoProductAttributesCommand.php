<?php

namespace Grayloon\MagentoStorage\Console;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Support\MagentoProductAttributes;
use Illuminate\Console\Command;

class SyncMagentoProductAttributesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync-product-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs the product attribute data from the Magneto 2 API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $totalCount = (new MagentoProductAttributes())->count();
        $totalPages = ceil(($totalCount / 50) + 1);

        $progress = $this->output->createProgressBar($totalCount);
        $progress->start();

        for ($currentPage = 1; $totalPages > $currentPage; $currentPage++) {
            $attributes = (new Magento())->api('productAttributes')
                ->all(50, $currentPage);

            if (! $attributes->successful() || ! $attributes->json()['items']) {
                throw new Exception($attributes['message'] ?? 'An unknown error has occurred retrieving product attributes.');
            }

            foreach ($attributes->json()['items'] as $attribute) {
                $progress->advance();
                (new MagentoProductAttributes())->updateOrCreate($attribute);
            }
        }

        $progress->finish();
    }
}
