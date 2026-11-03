<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Config;

use App\Modules\Barakaartcentre\Libraries\BarakaAdminService;
use App\Modules\Barakaartcentre\Libraries\BarakaPublicService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Baraka Art Centre module services in the CodeIgniter service container.
 *
 * @package App\Modules\Barakaartcentre\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for BarakaPublicService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return BarakaPublicService
     */
    public static function barakaPublicService(bool $getShared = true): BarakaPublicService
    {
        if ($getShared) {
            /** @var BarakaPublicService $service */
            $service = static::getSharedInstance('barakaPublicService');

            return $service;
        }

        return new BarakaPublicService();
    }

    /**
     * Factory method for BarakaAdminService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return BarakaAdminService
     */
    public static function barakaAdminService(bool $getShared = true): BarakaAdminService
    {
        if ($getShared) {
            /** @var BarakaAdminService $service */
            $service = static::getSharedInstance('barakaAdminService');

            return $service;
        }

        return new BarakaAdminService();
    }
}
