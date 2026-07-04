<?php

use Illuminate\Support\Facades\Http;
use Nugsoft\HikBridge\Exceptions\AuthenticationException;
use Nugsoft\HikBridge\Exceptions\ForbiddenException;
use Nugsoft\HikBridge\Exceptions\NotFoundException;
use Nugsoft\HikBridge\Exceptions\RateLimitException;
use Nugsoft\HikBridge\Exceptions\ServerException;
use Nugsoft\HikBridge\Exceptions\ValidationException;
use Nugsoft\HikBridge\HikBridgeClient;

beforeEach(function () {
    $this->client = $this->app->make(HikBridgeClient::class);
});

it('returns decoded json on get', function () {
    Http::fake(['*' => Http::response(['data' => ['id' => 1]], 200)]);

    $result = $this->client->get('/v1/business');

    expect($result)->toBe(['data' => ['id' => 1]]);
});

it('attaches bearer token to every request', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $this->client->get('/v1/business');

    Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer hbk_test_key'));
});

it('throws AuthenticationException on 401', function () {
    Http::fake(['*' => Http::response(['message' => 'Unauthenticated.'], 401)]);

    $this->client->get('/v1/business');
})->throws(AuthenticationException::class);

it('throws ForbiddenException on 403', function () {
    Http::fake(['*' => Http::response(['message' => 'Forbidden.'], 403)]);

    $this->client->get('/v1/business');
})->throws(ForbiddenException::class);

it('throws NotFoundException on 404', function () {
    Http::fake(['*' => Http::response(['message' => 'Not found.'], 404)]);

    $this->client->get('/v1/persons/9999');
})->throws(NotFoundException::class);

it('throws ValidationException on 422 and carries field errors', function () {
    Http::fake(['*' => Http::response([
        'message' => 'The given data was invalid.',
        'errors'  => ['person_code' => ['already taken']],
    ], 422)]);

    $caught = null;
    try {
        $this->client->post('/v1/persons', []);
    } catch (ValidationException $e) {
        $caught = $e;
    }

    expect($caught)->toBeInstanceOf(ValidationException::class)
        ->and($caught->errors())->toBe(['person_code' => ['already taken']]);
});

it('throws RateLimitException on 429', function () {
    Http::fake(['*' => Http::response(['message' => 'Too many requests.'], 429)]);

    $this->client->get('/v1/events');
})->throws(RateLimitException::class);

it('throws ServerException on 500', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error.'], 500)]);

    $this->client->get('/v1/business');
})->throws(ServerException::class);
