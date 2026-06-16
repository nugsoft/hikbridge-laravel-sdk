<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class DeviceResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    public function list(array $params = []): array
    {
        return $this->client->get('/v1/devices', $params);
    }

    public function get(int $deviceId): array
    {
        return $this->client->get("/v1/devices/{$deviceId}");
    }
}
