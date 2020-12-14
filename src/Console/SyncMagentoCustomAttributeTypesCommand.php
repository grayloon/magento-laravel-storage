<?php

namespace Grayloon\MagentoStorage\Console;

use Exception;
use Grayloon\MagentoStorage\Jobs\UpdateProductAttributeGroup;
use Grayloon\MagentoStorage\Models\MagentoCustomAttributeType;
use Illuminate\Console\Command;

class SyncMagentoCustomAttributeTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync-custom-attribute-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs the saved custom attribute types with the Magneto 2 API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $types = MagentoCustomAttributeType::get()
            ->each(fn ($type) => UpdateProductAttributeGroup::dispatch($type));

        if ($types->isEmpty()) {
            throw new Exception('No Attribute Types Exist. Please import types by importing products first.');
        }

        $this->info('Success. Launched several jobs to sync each custom attribute type.');
    }
}
