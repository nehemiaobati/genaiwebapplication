<?php

declare(strict_types=1);

namespace App\Modules\Ollama\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a user's Ollama configuration settings.
 *
 * @package App\Modules\Ollama\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property bool $assistant_mode_enabled
 * @property bool $stream_output_enabled
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class OllamaUserSetting extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                     => 'integer',
        'user_id'                => 'integer',
        'assistant_mode_enabled' => 'boolean',
        'stream_output_enabled'  => 'boolean',
    ];
}
