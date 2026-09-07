<?php

declare(strict_types=1);

namespace App\Modules\Admin\Config;

use App\Modules\Admin\Libraries\AdminService;
use App\Modules\Admin\Libraries\CampaignService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Admin module services in the CodeIgniter service container.
 *
 * @package App\Modules\Admin\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for AdminService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return AdminService
     */
    public static function adminService(bool $getShared = true): AdminService
    {
        if ($getShared) {
            /** @var AdminService $service */
            $service = static::getSharedInstance('adminService');

            return $service;
        }

        return new AdminService();
    }

    /**
     * Factory method for CampaignService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return CampaignService
     */
    public static function campaignService(bool $getShared = true): CampaignService
    {
        if ($getShared) {
            /** @var CampaignService $service */
            $service = static::getSharedInstance('campaignService');

            return $service;
        }

        return new CampaignService();
    }
}
