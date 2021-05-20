<?php

namespace App\Console\Commands;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoCustomerGroup;
use Grayloon\MagentoStorage\Support\HasMagentoCustomerGroups;
use Illuminate\Console\Command;

class SyncMagentoCustomerGroups extends Command
{
    use HasMagentoCustomerGroups;

    protected $signature = 'magento:sync-customer-groups';
    protected $description = 'Syncs the Magento Customer Groups from the Magento API.';

    protected $bar;
    protected $count;
    protected $pages;
    protected $pageSize = 100;
    protected $magento;

    public function handle(Magento $magento)
    {
        $this->magento = $magento;

        $this->info('Retrieving Magento Customer Groups from Magento...');
        $this->resolveTotalGroups();
        $this->info("{$this->count} Groups Found.");

        if (! $this->count) {
            return $this->error('No Customer Groups found. Sync Customer Group cancelled.');
        }

        $this->newLine();
        $this->bar->start();

        for ($currentPage = 1; $this->pages > $currentPage; $currentPage++) {
            $groups = $this->magento->api('customerGroups')
                ->search($this->pageSize, $currentPage);

            if (! $groups->ok()) {
                throw new Exception('Unable to locate Customer Groups.');
            }

            $this->updateOrCreateCustomerGroups($groups);
        }

        $this->bar->finish();
        $this->newLine();
        $this->newLine();
        $this->info(MagentoCustomerGroup::count() . ' groups synced from the Magento API to your application.');
    }

    /**
     * The total count of Customer Groups.
     *
     * @throws \Exception
     * @return $this
     */
    protected function resolveTotalGroups()
    {
        if (config('magento.store_code')) {
            $this->newLine();
            $this->info('Default Store Code: "'. config('magento.store_code') .'" is configured in your env.');
            $this->info('We will only sync those customer groups associated with "'. config('magento.store_code') .'".');
        }

        $groups = $this->magento->api('customerGroups')->search();

        if (! $groups->ok() || $groups->json()['total_count']) {
            throw new Exception('Unable to locate Customer Groups.');
        }

        $this->count = $groups->json()['total_count'];
        $this->bar = $this->output
            ->createProgressBar($this->count);
        $this->pages = ceil(($this->count / $this->pageSize) + 1);

        return $this;
    }
}
