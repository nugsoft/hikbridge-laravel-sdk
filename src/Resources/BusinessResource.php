<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class BusinessResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    /**
     * Return the single business the calling API key belongs to.
     *
     * Requires ability: `business:read`.
     */
    public function get(): array
    {
        return $this->client->get('/v1/business');
    }
}
