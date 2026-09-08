<?php

declare(strict_types=1);

namespace App\Modules\Ollama\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a saved user prompt template for Ollama.
 *
 * @package App\Modules\Ollama\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $prompt_text
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class OllamaPrompt extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'title'       => 'string',
        'prompt_text' => 'string',
    ];
}
