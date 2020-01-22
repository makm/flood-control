<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl\AttemptProvider;

/**
 * Class AttemptProviderInterface
 * @package Makm\FloodControl\AttemptProvider
 */
interface AttemptProviderInterface
{
    /**
     * @param string $actionKey
     * @param \DateTime $dateTime
     */
    public function push(string $actionKey, \DateTime $dateTime): void;

    /**
     * @param string $actionKey
     * @param \DateTime $afterDateTime
     * @return int
     */
    public function times(string $actionKey, \DateTime $afterDateTime): int;

    /**
     * @param $actionKey
     * @param \DateTime|null $beforeDateTime
     * @return void
     */
    public function purge($actionKey, \DateTime $beforeDateTime = null): void;
}
