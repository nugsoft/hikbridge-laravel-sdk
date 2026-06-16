<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;
use Nugsoft\HikBridge\PendingOperation;

class PersonResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    public function list(array $params = []): array
    {
        return $this->client->get('/v1/persons', $params);
    }

    public function get(int $personId): array
    {
        return $this->client->get("/v1/persons/{$personId}");
    }

    /**
     * Create a person.
     *
     * - Without `device_id` in $data: fans out to all active devices (202) → PendingOperation
     * - With `device_id` in $data: syncs to one device (201) → array
     */
    public function create(array $data): array|PendingOperation
    {
        $response = $this->client->postRaw('/v1/persons', $data);

        if ($response->status() === 202) {
            $body = $response->json();
            return new PendingOperation(
                operationId: $body['operation_id'],
                data: $body['data'] ?? [],
                client: $this->client,
            );
        }

        return $response->json() ?? [];
    }

    public function update(int $personId, array $data): array
    {
        return $this->client->put("/v1/persons/{$personId}", $data);
    }

    /**
     * Delete a person from all devices (always async → PendingOperation).
     */
    public function delete(int $personId): PendingOperation
    {
        $response = $this->client->deleteRaw("/v1/persons/{$personId}");
        $body     = $response->json();

        return new PendingOperation(
            operationId: $body['operation_id'],
            data: $body['data'] ?? [],
            client: $this->client,
        );
    }

    /**
     * Delete a person from one specific device (sync).
     */
    public function deleteFromDevice(int $personId, int $deviceId): array
    {
        return $this->client->deleteWithBody("/v1/persons/{$personId}", [
            'device_id' => $deviceId,
        ]);
    }
}
