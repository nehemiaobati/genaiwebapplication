<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Models;

use CodeIgniter\Model;
use App\Modules\OpenRouter\Entities\OpenRouterAGIEntity;

/**
 * Manages storage and retrieval of OpenRouter conversational entities.
 */
class OpenRouterEntityModel extends Model
{
    protected $table            = 'openrouter_entities';
    protected $primaryKey       = 'id';
    protected $returnType       = OpenRouterAGIEntity::class;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'user_id',
        'entity_key',
        'name',
        'type',
        'access_count',
        'relevance_score',
        'mentioned_in',
        'relationships'
    ];

    /**
     * Finds an entity by its key for a specific user.
     */
    public function findByUserAndKey(int $userId, string $entityKey): ?OpenRouterAGIEntity
    {
        return $this->where('user_id', $userId)
            ->where('entity_key', $entityKey)
            ->first();
    }
}
