<?php

namespace Grayloon\MagentoStorage\Console;

use Grayloon\MagentoStorage\Models\MagentoCustomAttribute;
use Illuminate\Console\Command;

class MagentoCleanAttributesCommand extends Command
{
    protected $signature = 'magento:clean';

    protected $description = 'Cleans up the database by removing missing relationships.';

    protected $deleted = 0;
    protected $attributes;
    protected $count;

    public function handle()
    {
        $count = MagentoCustomAttribute::count();

        $this->info('Checking ' . $count . ' custom attributes...');

        $this->attributes = MagentoCustomAttribute::with('attributable')
            ->cursor();

        $this->withProgressBar($this->attributes, function ($attribute) {
            if (! $attribute->attributable) {
                $attribute->delete();

                $this->deleted++;
            }
        });

        $this->newLine();
        $this->info($this->deleted . ' attributes with missing attributable relationships removed.');
    }
}
