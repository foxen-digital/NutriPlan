<?php

declare(strict_types=1);

namespace App\Services\Clients;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OpenAiClient
{
    public function __construct(private readonly string $apiKey, private string $model = 'gpt-4o-mini')
    {
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getChatCompletion(array $messages, array $options = []): Response
    {
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], $options);

        return Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', $payload);
    }
}
