<?php

declare(strict_types=1);

namespace App\Modules\OpenRouter\Libraries;

use App\Modules\OpenRouter\Config\OpenRouterAGI;

/**
 * Handles the generation of vector embeddings via the OpenRouter API.
 */
class OpenRouterEmbeddingService
{
    private ?string $apiKey;
    private string $modelId;
    private string $apiUrl = 'https://openrouter.ai/api/v1/embeddings';
    private bool $isEnabled;

    public function __construct()
    {
        $this->apiKey = env('OPENROUTER_API_KEY');
        $config = config(OpenRouterAGI::class);
        $this->modelId = $config->embeddingModel;
        $this->isEnabled = $config->enableEmbeddings;
    }

    /**
     * Converts text into a vector embedding.
     *
     * @param string|array $input The text or array of texts to embed.
     * @return array|null The vector(s) as an array, or null on error.
     */
    public function getEmbedding(string|array $input): ?array
    {
        if (!$this->isEnabled || empty($this->apiKey)) {
            return null;
        }

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->modelId,
                    'input' => $input,
                ],
                'http_errors' => false,
            ]);

            $body = $response->getBody();
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                log_message('error', "[OpenRouterEmbeddingService] HTTP {$statusCode} error. Response: {$body}");
                return null;
            }

            $decoded = json_decode($body, true);

            // OpenRouter returns list of embeddings
            if (is_array($input)) {
                return $decoded['data'] ?? null;
            }

            return $decoded['data'][0]['embedding'] ?? null;
        } catch (\Exception $e) {
            log_message('error', "[OpenRouterEmbeddingService] Exception: " . $e->getMessage());
            return null;
        }
    }
}
