<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\MagentoStorage\Models\MagentoProduct;
use Grayloon\MagentoStorage\Models\MagentoProductMedia;
use Grayloon\MagentoStorage\Jobs\DownloadMagentoProductImage;

trait HasMediaEntries
{
    /**
     * Store image record and launch a job to download an image to the Laravel application.
     *
     * @param  array  $image
     * @param  \Grayloon\Magento\Models\MagentoProduct  $product
     * @return void
     */
    public function downloadProductImages($images, MagentoProduct $product)
    {
        $product->images()->delete();
        
        foreach ($images as $image) {
            MagentoProductMedia::updateOrCreate([
                'id'         => $image['id'],
                'product_id' => $product->id,
            ], [
                'media_type' => $image['media_type'],
                'label'      => $image['label'],
                'position'   => $image['position'],
                'disabled'   => $image['disabled'],
                'types'      => $image['types'],
                'file'       => $image['file'],
                'synced_at'  => now(),
            ]);
            DownloadMagentoProductImage::dispatch($image['file']);
        }

        return $this;
    }
}
