<?php

use Illuminate\Support\Facades\Http;
use Nugsoft\HikBridge\Facades\HikBridge;

it('returns the signing secret on webhook creation', function () {
    Http::fake(['*/v1/webhooks' => Http::response([
        'data' => [
            'id'     => 1,
            'url'    => 'https://example.com/hook',
            'secret' => 'whsec_abc123',
        ],
    ], 201)]);

    $result = HikBridge::webhooks()->create([
        'url'         => 'https://example.com/hook',
        'event_types' => ['access.event'],
        'is_active'   => true,
    ]);

    expect($result['data']['secret'])->toBe('whsec_abc123');
});

it('sends a test ping to the webhook endpoint', function () {
    Http::fake(['*/v1/webhooks/1/test' => Http::response(['status' => 'ok'], 200)]);

    $result = HikBridge::webhooks()->sendTestPing(1);

    expect($result['status'])->toBe('ok');
});

it('returns delivery history for a webhook', function () {
    Http::fake(['*/v1/webhooks/1/deliveries' => Http::response(['data' => []], 200)]);

    $result = HikBridge::webhooks()->deliveries(1);

    expect($result)->toHaveKey('data');
});
