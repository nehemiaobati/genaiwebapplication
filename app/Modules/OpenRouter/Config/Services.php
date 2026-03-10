<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\OpenRouter\Libraries\OpenRouterService;
use App\Modules\OpenRouter\Libraries\OpenRouterMemoryService;
use App\Modules\OpenRouter\Libraries\OpenRouterPandocService;
use App\Modules\OpenRouter\Libraries\OpenRouterDocumentService;

/**
 * OpenRouter Module Services Configuration.
 */
class Services extends BaseService
{
    /**
     * The main OpenRouter service.
     *
     * @param bool $getShared
     * @return OpenRouterService
     */
    public static function openRouterService(bool $getShared = true): OpenRouterService
    {
        if ($getShared) {
            return static::getSharedInstance('openRouterService');
        }
        return new OpenRouterService();
    }

    /**
     * The Memory service, user-specific.
     *
     * @param int  $userId
     * @param bool $getShared
     * @return OpenRouterMemoryService
     */
    public static function openRouterMemory(int $userId, bool $getShared = false): OpenRouterMemoryService
    {
        if ($getShared) {
            return static::getSharedInstance('openRouterMemory', $userId);
        }
        return new OpenRouterMemoryService($userId);
    }

    /**
     * Pandoc wrapper for document conversion.
     *
     * @param bool $getShared
     * @return OpenRouterPandocService
     */
    public static function openRouterPandocService(bool $getShared = true): OpenRouterPandocService
    {
        if ($getShared) {
            return static::getSharedInstance('openRouterPandocService');
        }
        return new OpenRouterPandocService();
    }

    /**
     * Self-contained document generation service (PDF/DOCX).
     * Uses Pandoc → Dompdf/PHPWord fallback. No cross-module deps.
     *
     * @param bool $getShared
     * @return OpenRouterDocumentService
     */
    public static function openRouterDocumentService(bool $getShared = true): OpenRouterDocumentService
    {
        if ($getShared) {
            return static::getSharedInstance('openRouterDocumentService');
        }
        return new OpenRouterDocumentService();
    }
}
