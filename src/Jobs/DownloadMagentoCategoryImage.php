<?php

namespace Grayloon\MagentoStorage\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DownloadMagentoCategoryImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The associated category image path.
     *
     * @var string
     */
    public $path;

    /**
     * The Magento Category.
     *
     * @var \Grayloon\Magento\Models\MagentoCategory
     */
    public $category;

    /**
     * The fully constructed URL to download the image.
     *
     * @var string
     */
    public $fullUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, $category)
    {
        $this->path = $path;
        $this->category = $category;

        $this->fullUrl = config('magento.base_url').$this->path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $contents = file_get_contents($this->fullUrl);
        } catch (Exception $e) {
            throw new Exception('Failed to download image for Category: '.$this->category->id.'  Image URL: '.$this->fullUrl);
        }

        Storage::put('public/category'.$this->path, $contents);
    }
}
