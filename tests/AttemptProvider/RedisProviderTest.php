<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl\Tests\AttemptProvider;

use Makm\FloodControl\AttemptProvider\RedisProvider;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * Class RedisProviderTest
 * @package Makm\FloodControl\Tests\AttemptPrivider
 */
class RedisProviderTest extends TestCase
{
    private RedisProvider $provider;
    /**
     * @var Redis
     */
    private $redis;

    public function setUp(): void
    {
        $this->redis = $this->createMock(Redis::class);
        $this->provider = new RedisProvider($this->redis);
    }

    /**
     * @throws \Exception
     */
    public function testPushAndTimes(): void
    {
        $testKey = 'test-key';
        $this->provider->purge($testKey);

        // base tests
        $this->redis->expects($this->exactly(3))->method('zCount')->willReturn(1, 2, 2);
        $this->redis->expects($this->exactly(3))->method('zAdd');

        $this->provider->push($testKey, new \DateTime);
        $times = $this->provider->times($testKey, new \DateTime('-1 month'));
        $this->assertEquals(1, $times);

        $this->provider->push($testKey, new \DateTime);
        $times = $this->provider->times($testKey, new \DateTime('-1 month'));
        $this->assertEquals(2, $times);

        $this->provider->push($testKey, new \DateTime('-2 month'));
        $times = $this->provider->times($testKey, new \DateTime('-1 month'));
        $this->assertEquals(2, $times);
    }

    /**
     * @throws \Exception
     */
    public function testPurge(): void
    {
        $action = 'test-purge-1';
        $this->provider->purge($action);

        //purge all test
        $this->redis->expects($this->exactly(2))->method('zCount')->willReturn(3, 0);
        $this->redis->expects($this->exactly(3))->method('zAdd');

        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(3, $times);

        $this->provider->purge($action);
        $times = $this->provider->times($action, new \DateTime('-1 month'));
        $this->assertEquals(0, $times);
    }

    /**
     * @throws \Exception
     */
    public function testPurgeFromDate(): void
    {
        $action = 'test-purge-2';
        $this->provider->purge($action);

        $this->redis->expects($this->exactly(2))->method('zCount')->willReturn(2, 1);
        $this->redis->expects($this->exactly(2))->method('zAdd');


        $this->provider->push($action, new \DateTime);
        $this->provider->push($action, new \DateTime('-1 month'));
        $times = $this->provider->times($action, new \DateTime('-2 month'));
        $this->assertEquals(2, $times);

        $this->provider->purge($action, new \DateTime('-2 week'));
        $times = $this->provider->times($action, new \DateTime('-2 month'));
        $this->assertEquals(1, $times);
    }
}
