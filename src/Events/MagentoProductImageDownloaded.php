<?php

namespace Grayloon\MagentoStorage\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MagentoProductImageDownloaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The location of the stored saved image.
     *
     * @var string
     */
    public $image;
    
    /**
     * Create a new event instance.
     *
     * @param  string  $image
     * @return void
     */
    public function __construct($image)
    {
        $this->image = $image;
    }
}
