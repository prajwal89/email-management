<?php

namespace Prajwal89\EmailManagement\Tests;

use Prajwal89\EmailManagement\EmailManagementServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EmailManagementServiceProvider::class,
        ];
    }
}
