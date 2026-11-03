<?php

declare(strict_types=1);

namespace App\Modules\Account\Config;

use App\Modules\Account\Libraries\AccountService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Account module services in the CodeIgniter service container.
 *
 * @package App\Modules\Account\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for AccountService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return AccountService
     */
    public static function accountService(bool $getShared = true): AccountService
    {
        if ($getShared) {
            /** @var AccountService $service */
            $service = static::getSharedInstance('accountService');

            return $service;
        }

        return new AccountService();
    }
}
