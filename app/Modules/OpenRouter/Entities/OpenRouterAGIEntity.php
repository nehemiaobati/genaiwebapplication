<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a conversational entity (keyword/concept) for OpenRouter memory.
 *
 * @package App\Modules\OpenRouter\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string $entity_key
 * @property string $name
 * @property string|null $type
 * @property int $access_count
 * @property float $relevance_score
 * @property array<int, string>|null $mentioned_in
 * @property array<string, mixed>|null $relationships
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class OpenRouterAGIEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'access_count'    => 'integer',
        'relevance_score' => 'float',
        'mentioned_in'    => 'json-array',
        'relationships'   => 'json-array',
    ];
}
