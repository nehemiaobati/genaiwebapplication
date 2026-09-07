<?php

declare(strict_types=1);

namespace App\Modules\Affiliate\Config;

use App\Modules\Affiliate\Libraries\AffiliateLinkService;
use App\Modules\Affiliate\Models\AffiliateLinkModel;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Affiliate module services in the CodeIgniter service container.
 *
 * @package App\Modules\Affiliate\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for AffiliateLinkService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return AffiliateLinkService
     */
    public static function affiliateLinkService(bool $getShared = true): AffiliateLinkService
    {
        if ($getShared) {
            /** @var AffiliateLinkService $service */
            $service = static::getSharedInstance('affiliateLinkService');

            return $service;
        }

        return new AffiliateLinkService(new AffiliateLinkModel());
    }
}
