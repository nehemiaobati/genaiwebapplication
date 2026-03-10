<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Models;

use CodeIgniter\Model;
use App\Modules\OpenRouter\Entities\OpenRouterPrompt;

/**
 * Model for user-saved OpenRouter prompt templates.
 */
class OpenRouterPromptModel extends Model
{
    /** @var string */
    protected $table = 'openrouter_prompts';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var string */
    protected $returnType = OpenRouterPrompt::class;

    /** @var bool */
    protected $useTimestamps = true;

    /** @var array<string> */
    protected $allowedFields = ['user_id', 'title', 'prompt_text'];
}
