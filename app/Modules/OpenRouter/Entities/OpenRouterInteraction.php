<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a single AI-user conversation turn.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $unique_id
 * @property string $timestamp
 * @property string $user_input_raw
 * @property string $ai_output
 * @property string $ai_output_raw
 * @property float  $relevance_score
 * @property string $last_accessed
 * @property array  $context_used_ids
 * @property array  $embedding
 * @property array  $keywords
 */
class OpenRouterInteraction extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'context_used_ids' => 'json-array',
        'embedding'        => 'json-array',
        'keywords'         => 'json-array',
    ];
}
