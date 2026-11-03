<?php

declare(strict_types=1);

namespace App\Modules\Portfolio\Config;

use App\Modules\Portfolio\Libraries\PortfolioService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Portfolio module services in the CodeIgniter service container.
 *
 * @package App\Modules\Portfolio\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for PortfolioService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return PortfolioService
     */
    public static function portfolioService(bool $getShared = true): PortfolioService
    {
        if ($getShared) {
            /** @var PortfolioService $service */
            $service = static::getSharedInstance('portfolioService');

            return $service;
        }

        return new PortfolioService();
    }
}
