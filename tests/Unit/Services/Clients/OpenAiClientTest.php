<?php

declare(strict_types=1);

use App\Services\Clients\OpenAiClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->defaultModel = 'gpt-3.5-turbo';
    $this->openAiClient = new OpenAiClient($this->apiKey, $this->defaultModel);
});

test('setModel updates the model', function () {
    // Act
    $result = $this->openAiClient->setModel('gpt-4');

    // Assert
    expect($result)->toBe($this->openAiClient);

    // Test model is used in the request
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello!'
                    ]
                ]
            ]
        ], 200)
    ]);

    $this->openAiClient->getChatCompletion([
        ['role' => 'user', 'content' => 'Hello']
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.openai.com/v1/chat/completions' &&
               $request['model'] === 'gpt-4';
    });
});

test('getChatCompletion sends correct payload', function () {
    // Arrange
    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant'],
        ['role' => 'user', 'content' => 'Hello']
    ];

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello!'
                    ]
                ]
            ]
        ], 200)
    ]);

    // Act
    $response = $this->openAiClient->getChatCompletion($messages);

    // Assert
    Http::assertSent(function ($request) use ($messages) {
        return $request->url() === 'https://api.openai.com/v1/chat/completions' &&
               $request['model'] === $this->defaultModel &&
               $request['messages'] === $messages;
    });
});

test('getChatCompletion sends additional options', function () {
    // Arrange
    $messages = [
        ['role' => 'user', 'content' => 'Hello']
    ];

    $options = [
        'temperature' => 0.2,
        'max_tokens' => 100,
        'response_format' => ['type' => 'json_object']
    ];

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello!'
                    ]
                ]
            ]
        ], 200)
    ]);

    // Act
    $response = $this->openAiClient->getChatCompletion($messages, $options);

    // Assert
    Http::assertSent(function ($request) use ($messages, $options) {
        return $request->url() === 'https://api.openai.com/v1/chat/completions' &&
               $request['model'] === $this->defaultModel &&
               $request['messages'] === $messages &&
               $request['temperature'] === $options['temperature'] &&
               $request['max_tokens'] === $options['max_tokens'] &&
               $request['response_format'] === $options['response_format'];
    });
});

test('getChatCompletion uses proper authentication', function () {
    // Arrange
    $messages = [
        ['role' => 'user', 'content' => 'Hello']
    ];

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello!'
                    ]
                ]
            ]
        ], 200)
    ]);

    // Act
    $response = $this->openAiClient->getChatCompletion($messages);

    // Assert
    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', "Bearer {$this->apiKey}") &&
               $request->hasHeader('Content-Type', 'application/json');
    });
});
