<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap tests without reusing Laravel's local/production config cache.
     *
     * This child path cannot exist while phpunit.xml is a file, so Laravel must
     * load the PHPUnit environment instead of bootstrap/cache/config.php.
     */
    public function createApplication(): Application
    {
        $disabledConfigCache = dirname(__DIR__).DIRECTORY_SEPARATOR.'phpunit.xml'.DIRECTORY_SEPARATOR.'config.php';

        putenv("APP_CONFIG_CACHE={$disabledConfigCache}");
        $_ENV['APP_CONFIG_CACHE'] = $disabledConfigCache;
        $_SERVER['APP_CONFIG_CACHE'] = $disabledConfigCache;

        /** @var Application $app */
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $driver = $app['config']->get('database.connections.sqlite.driver');
        $database = $app['config']->get('database.connections.sqlite.database');

        if (! $app->environment('testing') || $connection !== 'sqlite' || $driver !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(sprintf(
                'SAFETY: Tests may only run using SQLite :memory:. Environment: %s; current connection: %s; driver: %s; SQLite database: %s.',
                var_export($app->environment(), true),
                var_export($connection, true),
                var_export($driver, true),
                var_export($database, true),
            ));
        }

        return $app;
    }
}
