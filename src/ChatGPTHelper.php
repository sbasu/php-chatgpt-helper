<?php

namespace ChatGPTHelper;

class ChatGPTHelper
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';
    private string $model = 'gpt-3.5-turbo';
    private array $defaultHeaders;
    private array $conversation = [];
    private int $maxTokens = 1000;
    private float $temperature = 0.7;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->defaultHeaders = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
    }

    /**
     * Set the model to use
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set maximum tokens for response
     */
    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    /**
     * Set temperature for creativity (0.0 - 2.0)
     */
    public function setTemperature(float $temperature): self
    {
        $this->temperature = max(0.0, min(2.0, $temperature));
        return $this;
    }

    /**
     * Send a single chat message
     */
    public function chat(string $message, array $options = []): array
    {
        $messages = [
            ['role' => 'user', 'content' => $message]
        ];

        return $this->sendChatRequest($messages, $options);
    }

    /**
     * Start or continue a conversation
     */
    public function conversation(string $message, array $options = []): array
    {
        $this->conversation[] = ['role' => 'user', 'content' => $message];
        
        $response = $this->sendChatRequest($this->conversation, $options);
        
        if (isset($response['choices'][0]['message'])) {
            $this->conversation[] = $response['choices'][0]['message'];
        }
        
        return $response;
    }

    /**
     * Set system prompt for conversation
     */
    public function setSystemPrompt(string $prompt): self
    {
        // Remove existing system message if any
        $this->conversation = array_filter($this->conversation, function($msg) {
            return $msg['role'] !== 'system';
        });
        
        // Add system message at the beginning
        array_unshift($this->conversation, ['role' => 'system', 'content' => $prompt]);
        
        return $this;
    }

    /**
     * Clear conversation history
     */
    public function clearConversation(): self
    {
        $this->conversation = [];
        return $this;
    }

    /**
     * Get conversation history
     */
    public function getConversation(): array
    {
        return $this->conversation;
    }

    /**
     * Generate completion for a prompt
     */
    public function complete(string $prompt, array $options = []): array
    {
        $data = array_merge([
            'model' => $this->model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ], $options);

        return $this->makeRequest('/chat/completions', $data);
    }

    /**
     * Generate image using DALL-E
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        $data = array_merge([
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024'
        ], $options);

        return $this->makeRequest('/images/generations', $data);
    }

    /**
     * Get available models
     */
    public function getModels(): array
    {
        return $this->makeRequest('/models', [], 'GET');
    }

    /**
     * Estimate token count (rough approximation)
     */
    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token for English
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Send chat request with conversation context
     */
    private function sendChatRequest(array $messages, array $options = []): array
    {
        $data = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ], $options);

        return $this->makeRequest('/chat/completions', $data);
    }

    /**
     * Make HTTP request to OpenAI API
     */
    private function makeRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method
        ]);

        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMessage = $decodedResponse['error']['message'] ?? 'Unknown API error';
            throw new \Exception("OpenAI API Error ({$httpCode}): " . $errorMessage);
        }

        return $decodedResponse;
    }

    /**
     * Extract just the text content from a chat response
     */
    public function getResponseText(array $response): string
    {
        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Get usage statistics from response
     */
    public function getUsage(array $response): array
    {
        return $response['usage'] ?? [];
    }
}