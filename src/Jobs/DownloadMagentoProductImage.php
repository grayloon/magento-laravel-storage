<?php

namespace Grayloon\MagentoStorage\Jobs;

use Exception;
use Grayloon\MagentoStorage\Events\MagentoProductImageDownloaded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DownloadMagentoProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The end uri of the image from Magento.
     *
     * @var string
     */
    public $uri;

    /**
     * The Magento directory where images are located.
     *
     * @var string
     */
    public $directory;

    /**
     * The fully constructed URL to download the image.
     *
     * @var string
     */
    public $fullUrl;

    /**
     * The provided product sku.
     *
     * @var string
     */
    public $sku;

    /**
     * Determine if the image already exists.
     *
     * @var bool
     */
    protected $alreadyExists = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uri, $sku, $directory = null)
    {
        $this->uri = $uri;
        $this->sku = $sku;

        $this->directory = $this->directory ?: '/pub/media/catalog/product';
        $this->fullUrl = config('magento.base_url').$this->directory.$this->uri;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Storage::exists('public/product/'.$this->uri)) {
            $this->alreadyExists = true;
            return $this;
        }

        try {
            $contents = file_get_contents($this->fullUrl);
        } catch (Exception $e) {
            throw new Exception('Failed to download image for SKU: '.$this->sku.' Image URL: '.$this->fullUrl);
        }

        Storage::put('public/product/'.$this->uri, $contents);

        event(new MagentoProductImageDownloaded('public/product/'.$this->uri));
    }
}
