<?php

namespace App\Modules\OpenRouter\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration for the OpenRouter AGI (Artificial General Intelligence) system.
 */
class OpenRouterAGI extends BaseConfig
{
    // --- Embedding Configuration ---

    /**
     * Master switch to enable or disable the entire vector/semantic search system.
     */
    public bool $enableEmbeddings = true;

    /**
     * The model used for embeddings.
     * OpenRouter supports many, including free variants.
     */
    public string $embeddingModel = 'text-embedding-ada-002:free';

    // --- Memory Logic Configuration ---

    /**
     * The amount to increase a memory's 'relevance_score' when it is successfully used.
     */
    public float $rewardScore = 0.5;

    /**
     * The base amount to decrease a memory's 'relevance_score' during each new interaction.
     */
    public float $decayScore = 0.05;

    /**
     * The starting 'relevance_score' for any new memory created.
     */
    public float $initialScore = 1.0;

    /**
     * The maximum number of interactions to keep in memory.
     */
    public int $pruningThreshold = 500;

    /**
     * The maximum number of characters to include in the context sent to the AI.
     */
    public int $contextCharBudget = 40000;

    /**
     * The number of most recent interactions to ALWAYS include in the context.
     */
    public int $forcedRecentInteractions = 3;

    // --- Hybrid Search Tuning ---

    /**
     * Dial that balances keyword search (0.0) against vector search (1.0).
     */
    public float $hybridSearchAlpha = 0.5;

    /**
     * The number of top results to fetch from the semantic (vector) search stage.
     */
    public int $vectorSearchTopK = 15;

    // --- Advanced Scoring & Relationships ---

    /**
     * Bonus given to a new memory if it contains a novel keyword.
     */
    public float $noveltyBonus = 0.3;

    /**
     * Amount to increase the strength of the connection between two keywords.
     */
    public float $relationshipStrengthIncrement = 0.1;

    /**
     * Multiplier that reduces the 'decayScore' for memories related to the current topic.
     */
    public float $recentTopicDecayModifier = 0.1;

    // --- NLP Configuration ---
    /**
     * Stop words to filter out during keyword extraction.
     */
    public array $nlpStopWords = [
        'a',
        'an',
        'and',
        'are',
        'as',
        'at',
        'be',
        'by',
        'for',
        'from',
        'has',
        'he',
        'in',
        'is',
        'it',
        'its',
        'of',
        'on',
        'that',
        'the',
        'to',
        'was',
        'were',
        'will',
        'with',
        'what',
        'when',
        'where',
        'who',
        'why',
        'how',
        'my',
        'we',
        'user',
        'note',
        'system',
        'please',
        'a',
        'abbr',
        'address',
        'area',
        'article',
        'aside',
        'audio',
        'b',
        'base',
        'bdi',
        'bdo',
        'blockquote',
        'body',
        'br',
        'button',
        'canvas',
        'caption',
        'cite',
        'code',
        'col',
        'colgroup',
        'data',
        'datalist',
        'dd',
        'del',
        'details',
        'dfn',
        'dialog',
        'div',
        'dl',
        'dt',
        'em',
        'embed',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'header',
        'hr',
        'html',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'legend',
        'li',
        'link',
        'main',
        'map',
        'mark',
        'meta',
        'meter',
        'nav',
        'noscript',
        'object',
        'ol',
        'optgroup',
        'option',
        'output',
        'p',
        'param',
        'picture',
        'pre',
        'progress',
        'q',
        'rp',
        'rt',
        'ruby',
        's',
        'samp',
        'script',
        'section',
        'select',
        'small',
        'source',
        'span',
        'strong',
        'style',
        'sub',
        'summary',
        'sup',
        'table',
        'tbody',
        'td',
        'template',
        'textarea',
        'tfoot',
        'th',
        'thead',
        'time',
        'tr',
        'track',
        'u',
        'ul',
        'var',
        'video',
        'wbr',
        'nbsp'
    ];
}
