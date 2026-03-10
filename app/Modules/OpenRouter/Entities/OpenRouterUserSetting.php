<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a user's OpenRouter settings.
 *
 * @property int  $id
 * @property int  $user_id
 * @property bool $assistant_mode_enabled
 * @property bool $stream_output_enabled
 */
class OpenRouterUserSetting extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'assistant_mode_enabled' => 'boolean',
        'stream_output_enabled'  => 'boolean',
    ];
}
