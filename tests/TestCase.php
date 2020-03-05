<?php

namespace Lobotomised\Dbbackup\Test;

use Lobotomised\Dbbackup\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }
}
