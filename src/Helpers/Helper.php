<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Helpers;

use InvalidArgumentException;
use ReflectionClass;

class Helper
{
    public static function getCommandSignature(string $className): ?string
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Class {$className} does not exist.");
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->hasProperty('signature')) {
            $property = $reflection->getProperty('signature');
            $property->setAccessible(true);

            return 'php artisan ' . $property->getValue(new $className);
        }

        return null;
    }
}
