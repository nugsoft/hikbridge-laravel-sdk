<?php

namespace Nugsoft\HikBridge\Facades;

use Illuminate\Support\Facades\Facade;
use Nugsoft\HikBridge\HikBridgeManager;
use Nugsoft\HikBridge\Resources\BiometricResource;
use Nugsoft\HikBridge\Resources\DeviceResource;
use Nugsoft\HikBridge\Resources\EventResource;
use Nugsoft\HikBridge\Resources\OperationResource;
use Nugsoft\HikBridge\Resources\OrganizationResource;
use Nugsoft\HikBridge\Resources\PersonResource;
use Nugsoft\HikBridge\Resources\WebhookResource;

/**
 * @method static OrganizationResource organization()
 * @method static DeviceResource        devices()
 * @method static PersonResource        persons()
 * @method static BiometricResource     biometrics(int $personId)
 * @method static EventResource         events()
 * @method static WebhookResource       webhooks()
 * @method static OperationResource     operations()
 *
 * @see HikBridgeManager
 */
class HikBridge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HikBridgeManager::class;
    }
}
