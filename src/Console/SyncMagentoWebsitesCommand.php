<?php

namespace Grayloon\MagentoStorage\Console;

use Exception;
use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Models\MagentoWebsite;
use Illuminate\Console\Command;

class SyncMagentoWebsitesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:sync-websites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves a list of Magento websites and syncs them with the database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $websites = (new Magento())->api('stores')
            ->websites();

        if (! $websites->ok()) {
            throw new Exception('Unable to retrieve website list.');
        }
        
        foreach ($websites->json() as $website) {
            MagentoWebsite::updateOrCreate(
                ['id' => $website['id']],
                [
                'code'      => $website['id'],
                'name'      => $website['name'],
                'synced_at' => now(),
            ]
            );
        }
    }
}
