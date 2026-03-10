<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a conversational entity (keyword/concept) for OpenRouter memory.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $entity_key
 * @property string $name
 * @property string|null $type
 * @property int    $access_count
 * @property float  $relevance_score
 * @property array  $mentioned_in
 * @property array  $relationships
 */
class OpenRouterAGIEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'mentioned_in'  => 'json-array',
        'relationships' => 'json-array',
    ];
}
