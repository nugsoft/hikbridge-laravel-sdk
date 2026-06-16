<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class OperationResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    /**
     * Poll the status of an async fan-out operation.
     * Returns per-device progress and overall status.
     */
    public function get(string $operationId): array
    {
        return $this->client->get("/v1/operations/{$operationId}");
    }
}
