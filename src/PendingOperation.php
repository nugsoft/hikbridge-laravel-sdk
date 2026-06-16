<?php

namespace Nugsoft\HikBridge;

use Nugsoft\HikBridge\Exceptions\HikBridgeException;

class PendingOperation
{
    public function __construct(
        public readonly string $operationId,
        public readonly array $data,
        private readonly HikBridgeClient $client,
    ) {}

    /**
     * Poll the operation until it reaches a terminal state or the timeout expires.
     *
     * @throws HikBridgeException if the operation fails or times out
     */
    public function waitUntilDone(int $timeout = 60, int $interval = 2): array
    {
        $elapsed = 0;

        while ($elapsed < $timeout) {
            $result = $this->client->get("/v1/operations/{$this->operationId}");
            $status = $result['data']['status'] ?? null;

            if ($status === 'completed') {
                return $result;
            }

            if ($status === 'failed') {
                throw new HikBridgeException(
                    "Operation {$this->operationId} failed: " . ($result['data']['error'] ?? 'unknown error')
                );
            }

            sleep($interval);
            $elapsed += $interval;
        }

        throw new HikBridgeException(
            "Operation {$this->operationId} did not complete within {$timeout} seconds."
        );
    }

    public function isPending(): bool
    {
        return true;
    }
}
