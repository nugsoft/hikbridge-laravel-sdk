<?php

namespace Nugsoft\HikBridge\Tests;

use Nugsoft\HikBridge\HikBridgeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [HikBridgeServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('hikbridge.base_url', 'http://hikbridge.test/api');
        $app['config']->set('hikbridge.api_key', 'hbk_test_key');
        $app['config']->set('hikbridge.timeout', 5);
        $app['config']->set('hikbridge.retry.times', 0);
    }
}
