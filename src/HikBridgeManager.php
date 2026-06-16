<?php

namespace Nugsoft\HikBridge;

use Nugsoft\HikBridge\Resources\BiometricResource;
use Nugsoft\HikBridge\Resources\DeviceResource;
use Nugsoft\HikBridge\Resources\EventResource;
use Nugsoft\HikBridge\Resources\OperationResource;
use Nugsoft\HikBridge\Resources\OrganizationResource;
use Nugsoft\HikBridge\Resources\PersonResource;
use Nugsoft\HikBridge\Resources\WebhookResource;

class HikBridgeManager
{
    public function __construct(private readonly HikBridgeClient $client) {}

    public function organization(): OrganizationResource
    {
        return new OrganizationResource($this->client);
    }

    public function devices(): DeviceResource
    {
        return new DeviceResource($this->client);
    }

    public function persons(): PersonResource
    {
        return new PersonResource($this->client);
    }

    public function biometrics(int $personId): BiometricResource
    {
        return new BiometricResource($this->client, $personId);
    }

    public function events(): EventResource
    {
        return new EventResource($this->client);
    }

    public function webhooks(): WebhookResource
    {
        return new WebhookResource($this->client);
    }

    public function operations(): OperationResource
    {
        return new OperationResource($this->client);
    }
}
