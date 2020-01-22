<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl\AttemptProvider;


/**
 * Class RedisProvider
 * @package Makm\FloodControl\AttemptProvider
 */
class RedisProvider implements AttemptProviderInterface
{
    private \Redis $redis;

    /**
     * RedisProvider constructor.
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @inheritDoc
     */
    public function push(string $actionKey, \DateTime $dateTime): void
    {
        $index = $dateTime->format('Uu');
        $this->redis->zAdd($actionKey, $index, $index);
    }

    /**
     * @inheritDoc
     */
    public function times(string $actionKey, \DateTime $afterDateTime): int
    {
        $first = (new \DateTime())->format('Uu');
        $last = $afterDateTime->format('Uu');

        return $this->redis->zCount($actionKey, $last, $first);
    }

    /**
     * @inheritDoc
     */
    public function purge(string $actionKey, \DateTime $beforeDateTime = null): void
    {
        if ($beforeDateTime === null) {
            $this->redis->del($actionKey);

            return;
        }
        $last = $beforeDateTime->format('Uu');

        $this->redis->zRemRangeByScore($actionKey, 0, $last);
    }
}
