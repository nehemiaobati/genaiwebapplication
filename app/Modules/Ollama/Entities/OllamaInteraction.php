<?php

declare(strict_types=1);

namespace App\Modules\Ollama\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a single conversation turn in Ollama.
 *
 * @package App\Modules\Ollama\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $prompt_hash
 * @property string $user_input
 * @property string $ai_response
 * @property string $ai_model
 * @property float $relevance_score
 * @property array<int, float>|null $embedding
 * @property array<int, string>|null $keywords
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class OllamaInteraction extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'relevance_score' => 'float',
        'embedding'       => 'json-array',
        'keywords'        => 'json-array',
    ];
}
