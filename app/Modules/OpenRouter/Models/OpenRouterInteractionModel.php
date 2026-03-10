<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Models;

use CodeIgniter\Model;
use App\Modules\OpenRouter\Entities\OpenRouterInteraction;

/**
 * Model for OpenRouter conversation history (interactions).
 */
class OpenRouterInteractionModel extends Model
{
    /** @var string */
    protected $table = 'openrouter_interactions';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var string */
    protected $returnType = OpenRouterInteraction::class;

    /** @var bool */
    protected $useTimestamps = true;

    /** @var array<string> */
    protected $allowedFields = [
        'user_id',
        'unique_id',
        'timestamp',
        'user_input_raw',
        'ai_output',
        'ai_output_raw',
        'relevance_score',
        'last_accessed',
        'context_used_ids',
        'embedding',
        'keywords',
    ];
}
