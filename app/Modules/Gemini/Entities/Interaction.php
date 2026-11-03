<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Interaction
 *
 * @package App\Modules\Gemini\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int $id
 * @property int $user_id
 * @property string $unique_id
 * @property \CodeIgniter\I18n\Time|null $timestamp
 * @property string $user_input_raw
 * @property string $ai_output
 * @property array<string, mixed>|null $ai_output_raw
 * @property float $relevance_score
 * @property \CodeIgniter\I18n\Time|null $last_accessed
 * @property array<int, string>|null $context_used_ids
 * @property array<int, float>|null $embedding
 * @property array<int, string>|null $keywords
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Interaction extends Entity
{
    protected $dates   = ['timestamp', 'last_accessed', 'created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'user_id' => 'integer',
        'relevance_score' => 'float',
        'context_used_ids' => 'json-array',
        'ai_output_raw' => 'json-array',
        'embedding' => 'json-array',
        'keywords' => 'json-array',
    ];
}
