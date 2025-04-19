<?php

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\{Auth, Event, RateLimiter};
use Illuminate\Validation\ValidationException;

test('login request validates required fields', function () {
    $request = new LoginRequest();
    
    $rules = $request->rules();
    
    expect($rules)->toHaveKeys(['email', 'password'])
        ->and($rules['email'])->toContain('required', 'string', 'email')
        ->and($rules['password'])->toContain('required', 'string');
});

test('login request is always authorized', function () {
    $request = new LoginRequest();
    
    expect($request->authorize())->toBeTrue();
});

test('authenticate succeeds with valid credentials', function () {
    $request = new LoginRequest();
    $request->merge([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturnFalse();
        
    Auth::shouldReceive('attempt')
        ->once()
        ->with([
            'email' => 'test@example.com',
            'password' => 'password',
        ], false)
        ->andReturnTrue();
        
    RateLimiter::shouldReceive('clear')
        ->once();
        
    $request->authenticate();
});

test('authenticate fails with invalid credentials', function () {
    $request = new LoginRequest();
    $request->merge([
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);
    
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturnFalse();
        
    Auth::shouldReceive('attempt')
        ->once()
        ->with([
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ], false)
        ->andReturnFalse();
        
    RateLimiter::shouldReceive('hit')
        ->once();
    
    try {
        $request->authenticate();
    } catch (ValidationException $e) {
        expect($e->errors()['email'][0])->toBe(trans('auth.failed'));
    }
});

test('ensure rate limiting works correctly', function () {
    $request = new LoginRequest();
    $request->merge(['email' => 'test@example.com']);
    
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturnTrue();
        
    RateLimiter::shouldReceive('availableIn')
        ->once()
        ->andReturn(60);
        
    Event::fake();
    
    try {
        $request->ensureIsNotRateLimited();
    } catch (ValidationException $e) {
        expect($e->errors()['email'][0])->toBe(trans('auth.throttle', [
            'seconds' => 60,
            'minutes' => 1,
        ]));
    }
        
    Event::assertDispatched(Lockout::class);
});

test('throttle key generation is correct', function () {
    $request = new LoginRequest();
    $request->merge(['email' => 'test@example.com']);
    $request->server->set('REMOTE_ADDR', '127.0.0.1');
    
    expect($request->throttleKey())
        ->toBe('test@example.com|127.0.0.1');
});
