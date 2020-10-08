<?php

namespace Grayloon\MagentoStorage\Events;

use Grayloon\MagentoStorage\Models\MagentoProduct;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MagentoProductSynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $product;

    /**
     * Create a new event instance.
     *
     * @param \Grayloon\MagentoStorage\Models\MagentoProduct $product
     * @return void
     */
    public function __construct(MagentoProduct $product)
    {
        $this->product = $product;
    }
}
