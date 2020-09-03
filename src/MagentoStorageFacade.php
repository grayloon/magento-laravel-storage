<?php

namespace Grayloon\MagentoStorage;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Grayloon\MagentoStorage\Skeleton\SkeletonClass
 */
class MagentoStorageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'magentoStorage';
    }
}
