<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;


use CrowdinApiClient\Crowdin;
use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;

abstract class RepositoryTestCase extends TestCase
{
    protected const int PROJECT_ID = 368815;

    protected static MockWebServer $server;
    protected static Crowdin $client;

    public static function setUpBeforeClass(): void
    {
        self::$server = new MockWebServer();
        self::$server->start();

        self::$client = new Crowdin([]);
        self::$client->setBaseUri(self::$server->getServerRoot() . '/api/v2');
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }
}
