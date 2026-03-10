<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a saved prompt template.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $title
 * @property string $prompt_text
 */
class OpenRouterPrompt extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];
}
