<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl\Tests\AttemptProvider;

use Makm\FloodControl\AttemptProvider\FileProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class FileProviderTest
 * @package Makm\FloodControl\Tests\AttemptPrivider
 */
class FileProviderTest extends TestCase
{
    /**
     * @var FileProvider
     */
    private FileProvider $provider;

    public function setUp(): void
    {
        $this->provider = new FileProvider();
    }

    /**
     * @throws \Exception
     */
    public function testPushAndTimes()
    {
        $action = 'test-push-1';
        $this->provider->purge($action);

        $this->provider->push($action, new \DateTime);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(1, $times);

        $this->provider->push($action, new \DateTime);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(2, $times);

        $this->provider->push($action, new \DateTime('-2 month'));
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(2, $times);

        $this->provider->purge($action);
    }

    /**
     * @throws \Exception
     */
    public function testPurge()
    {
        $action = 'test-purge-1';
        $this->provider->purge($action);

        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(3, $times);
        $this->provider->purge($action);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(0, $times);

        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime('-1 month'));

        $this->provider->purge($action, new \DateTime('-2 week'));
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(1, $times);

        $this->provider->purge($action);
    }

}
