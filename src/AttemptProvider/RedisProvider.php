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
    private const DATE_FORMAT_INDEX = 'Uu';
    private const DATE_FORMAT_VALUE = 'U.u';

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
        $index = $dateTime->format(self::DATE_FORMAT_INDEX);
        $value = $dateTime->format(self::DATE_FORMAT_VALUE);
        $this->redis->zAdd($actionKey, $index, $value);
    }

    /**
     * @inheritDoc
     */
    public function timesAndFirstDateTime(string $actionKey, \DateTime $afterDateTime): array
    {
        $last = (new \DateTime())->format(self::DATE_FORMAT_INDEX);
        $first = $afterDateTime->format(self::DATE_FORMAT_INDEX);
        $count = $this->redis->zCount($actionKey, $first, $last);
        $firstDateValues = $this->redis->zRangeByScore($actionKey, $first,$last,['limit' => [0, 1]]);

        $firstDateTime = $firstDateValues
            ? \DateTime::createFromFormat(self::DATE_FORMAT_VALUE, \array_shift($firstDateValues))
            : null;

        return [$count, $firstDateTime];
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
        $last = $beforeDateTime->format(self::DATE_FORMAT_INDEX);

        $this->redis->zRemRangeByScore($actionKey, 0, $last);
    }
}
