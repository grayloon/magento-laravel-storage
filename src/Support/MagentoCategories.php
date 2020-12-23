<?php

namespace Grayloon\MagentoStorage\Support;

use Grayloon\Magento\Magento;
use Grayloon\MagentoStorage\Jobs\DownloadMagentoCategoryImage;
use Grayloon\MagentoStorage\Models\MagentoCategory;

class MagentoCategories extends PaginatableMagentoService
{
    use HasCustomAttributes;

    /**
     * The amount of total categories.
     *
     * @return int
     */
    public function count()
    {
        $categories = (new Magento())->api('categories')
            ->all($this->pageSize, $this->currentPage)
            ->json();

        return $categories['total_count'];
    }

    /**
     * Updates categories from the Magento API.
     *
     * @param  array  $categories
     * @return void
     */
    public function updateCategories($categories)
    {
        if (! $categories) {
            return;
        }

        foreach ($categories as $apiCategory) {
            $this->updateCategory($apiCategory);
        }

        return $this;
    }

    /**
     * Download the uploaded magento category image, if available.
     *
     * @param  array  $customAttributes
     * @param  \Grayloon\Magento\Models\MagentoCategory $category
     *
     * @return void
     */
    protected function downloadCategoryImage($customAttributes, $category)
    {
        foreach ($customAttributes as $customAttribute) {
            if ($customAttribute['attribute_code'] === 'image') {
                DownloadMagentoCategoryImage::dispatch($customAttribute['value'], $category);
            }
        }

        return $this;
    }

    /**
     * Updates a category from the Magento API.
     *
     * @param  array  $apiCategory
     * @return \Grayloon\Magento\Models\MagentoCategory\
     */
    public function updateCategory($apiCategory)
    {
        $category = MagentoCategory::updateOrCreate(['id' => $apiCategory['id']], [
            'name'            => $apiCategory['name'],
            'slug'            => $this->findAttributeByKey('url_path', $apiCategory['custom_attributes']),
            'parent_id'       => $apiCategory['parent_id'] == 0 ? null : $apiCategory['parent_id'], // don't allow a parent ID of 0.
            'position'        => $apiCategory['position'],
            'is_active'       => $apiCategory['is_active'] ?? false,
            'level'           => $apiCategory['level'],
            'created_at'      => $apiCategory['created_at'],
            'updated_at'      => $apiCategory['updated_at'],
            'path'            => $apiCategory['path'],
            'include_in_menu' => $apiCategory['include_in_menu'],
            'synced_at'       => now(),
        ]);

        $this->syncCustomAttributes($apiCategory['custom_attributes'], $category);
        $this->downloadCategoryImage($apiCategory['custom_attributes'], $category);

        return $category;
    }
}
