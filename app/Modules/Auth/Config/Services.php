<?php

declare(strict_types=1);

namespace App\Modules\Auth\Config;

use App\Modules\Auth\Libraries\AuthService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Auth module services in the CodeIgniter service container.
 *
 * @package App\Modules\Auth\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for AuthService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return AuthService
     */
    public static function authService(bool $getShared = true): AuthService
    {
        if ($getShared) {
            /** @var AuthService $service */
            $service = static::getSharedInstance('authService');

            return $service;
        }

        return new AuthService();
    }
}
