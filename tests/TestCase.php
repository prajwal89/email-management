<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Tests;

use Prajwal89\EmailManagement\EmailManagementServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(
            random_bytes(32)
        ));
    }

    protected function getPackageProviders($app)
    {
        return [
            EmailManagementServiceProvider::class,
        ];
    }
}
