<?php

declare(strict_types=1);

namespace App\Modules\Contact\Config;

use App\Modules\Contact\Libraries\ContactService;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * Registers Contact module services in the CodeIgniter service container.
 *
 * @package App\Modules\Contact\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for ContactService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
     * @return ContactService
     */
    public static function contactService(bool $getShared = true): ContactService
    {
        if ($getShared) {
            /** @var ContactService $service */
            $service = static::getSharedInstance('contactService');

            return $service;
        }

        return new ContactService();
    }
}
