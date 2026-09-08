<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class UserSetting
 *
 * @package App\Modules\Gemini\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int $id
 * @property int $user_id
 * @property bool $assistant_mode_enabled
 * @property bool $voice_output_enabled
 * @property bool $stream_output_enabled
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class UserSetting extends Entity
{
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                     => 'integer',
        'user_id'                => 'integer',
        'assistant_mode_enabled' => 'boolean',
        'voice_output_enabled'   => 'boolean',
        'stream_output_enabled'  => 'boolean',
    ];
}
