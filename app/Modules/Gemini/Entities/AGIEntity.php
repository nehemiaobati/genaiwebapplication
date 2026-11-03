<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class AGIEntity
 *
 * @package App\Modules\Gemini\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int $id
 * @property int $user_id
 * @property string $entity_key
 * @property string $name
 * @property string $type
 * @property int $access_count
 * @property float $relevance_score
 * @property array<int, string>|null $mentioned_in
 * @property array<string, mixed>|null $relationships
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class AGIEntity extends Entity
{
    /**
     * Fields mutated into Time instances.
     *
     * @var array<int, string>
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Defined cast types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'access_count'    => 'integer',
        'relevance_score' => 'float',
        'mentioned_in'    => 'json-array',
        'relationships'   => 'json-array',
    ];
}
