<?php

use Illuminate\Support\Facades\Http;
use Nugsoft\HikBridge\Facades\HikBridge;
use Nugsoft\HikBridge\PendingOperation;

it('lists persons and forwards query params', function () {
    Http::fake(['*/v1/persons*' => Http::response(['data' => []], 200)]);

    $result = HikBridge::persons()->list(['per_page' => 10]);

    expect($result)->toBeArray();
    Http::assertSent(fn ($req) => str_contains($req->url(), 'per_page=10'));
});

it('gets a single person by id', function () {
    Http::fake(['*/v1/persons/57*' => Http::response(['data' => ['id' => 57]], 200)]);

    $result = HikBridge::persons()->get(57);

    expect($result['data']['id'])->toBe(57);
});

it('returns PendingOperation when creating without device_id (async 202)', function () {
    Http::fake(['*/v1/persons' => Http::response([
        'operation_id' => 'op_abc123',
        'data'         => ['id' => 10, 'person_code' => 'EMP001'],
    ], 202)]);

    $result = HikBridge::persons()->create([
        'person_code' => 'EMP001',
        'first_name'  => 'Amina',
        'last_name'   => 'Nakato',
        'status'      => 'active',
    ]);

    expect($result)
        ->toBeInstanceOf(PendingOperation::class)
        ->and($result->operationId)->toBe('op_abc123')
        ->and($result->isPending())->toBeTrue();
});

it('returns array when creating with device_id (sync 201)', function () {
    Http::fake(['*/v1/persons' => Http::response(['data' => ['id' => 10]], 201)]);

    $result = HikBridge::persons()->create([
        'person_code' => 'EMP001',
        'first_name'  => 'Amina',
        'device_id'   => 35,
    ]);

    expect($result)->toBeArray()
        ->and($result['data']['id'])->toBe(10);
});

it('sends a PUT request on update', function () {
    Http::fake(['*/v1/persons/57' => Http::response(['data' => ['id' => 57]], 200)]);

    HikBridge::persons()->update(57, ['status' => 'inactive']);

    Http::assertSent(fn ($req) => $req->method() === 'PUT');
});

it('returns PendingOperation on delete (async fan-out)', function () {
    Http::fake(['*/v1/persons/57' => Http::response([
        'operation_id' => 'op_del123',
        'data'         => ['id' => 57],
    ], 202)]);

    $result = HikBridge::persons()->delete(57);

    expect($result)
        ->toBeInstanceOf(PendingOperation::class)
        ->and($result->operationId)->toBe('op_del123');
});
