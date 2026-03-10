<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Models;

use CodeIgniter\Model;
use App\Modules\OpenRouter\Entities\OpenRouterUserSetting;

/**
 * Model for OpenRouter per-user settings.
 */
class OpenRouterUserSettingsModel extends Model
{
    /** @var string */
    protected $table = 'openrouter_user_settings';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var string */
    protected $returnType = OpenRouterUserSetting::class;

    /** @var bool */
    protected $useTimestamps = true;

    /** @var array<string> */
    protected $allowedFields = ['user_id', 'assistant_mode_enabled', 'stream_output_enabled'];
}
