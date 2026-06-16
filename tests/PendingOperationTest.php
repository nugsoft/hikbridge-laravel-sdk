<?php

use Illuminate\Support\Facades\Http;
use Nugsoft\HikBridge\Exceptions\HikBridgeException;
use Nugsoft\HikBridge\HikBridgeClient;
use Nugsoft\HikBridge\PendingOperation;

it('resolves when operation status becomes completed', function () {
    Http::fake([
        '*/v1/operations/op_123' => Http::sequence()
            ->push(['data' => ['status' => 'pending']], 200)
            ->push(['data' => ['status' => 'completed', 'results' => []]], 200),
    ]);

    $op     = new PendingOperation('op_123', [], $this->app->make(HikBridgeClient::class));
    $result = $op->waitUntilDone(timeout: 10, interval: 0);

    expect($result['data']['status'])->toBe('completed');
});

it('throws when operation status is failed', function () {
    Http::fake([
        '*/v1/operations/op_456' => Http::response([
            'data' => ['status' => 'failed', 'error' => 'Device unreachable'],
        ], 200),
    ]);

    $op = new PendingOperation('op_456', [], $this->app->make(HikBridgeClient::class));

    $op->waitUntilDone(timeout: 10, interval: 0);
})->throws(HikBridgeException::class, 'Device unreachable');

it('throws when timeout is exceeded before completion', function () {
    Http::fake([
        '*/v1/operations/op_789' => Http::response([
            'data' => ['status' => 'pending'],
        ], 200),
    ]);

    $op = new PendingOperation('op_789', [], $this->app->make(HikBridgeClient::class));

    $op->waitUntilDone(timeout: 1, interval: 2);
})->throws(HikBridgeException::class, 'did not complete within');

it('reports isPending as true', function () {
    $op = new PendingOperation('op_xyz', ['id' => 1], $this->app->make(HikBridgeClient::class));

    expect($op->isPending())->toBeTrue();
});
