<?php

use Illuminate\Support\Facades\Http;
use Nugsoft\HikBridge\Facades\HikBridge;

it('gets the business the API key belongs to', function () {
    Http::fake(['*/v1/business' => Http::response([
        'data' => ['id' => 27, 'name' => 'Acme Corp'],
    ], 200)]);

    $result = HikBridge::business()->get();

    expect($result['data']['id'])->toBe(27)
        ->and($result['data']['name'])->toBe('Acme Corp');

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v1/business'));
});
