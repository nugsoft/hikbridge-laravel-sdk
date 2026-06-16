<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class OrganizationResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    public function get(): array
    {
        return $this->client->get('/v1/organization');
    }
}
