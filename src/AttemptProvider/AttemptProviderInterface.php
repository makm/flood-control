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
     * push new action into list
     *
     * @param string $actionKey
     * @param \DateTime $dateTime
     */
    public function push(string $actionKey, \DateTime $dateTime): void;

    /**
     * times until date and first dateTime of action in period  pair
     *
     * @param string $actionKey
     * @param \DateTime $afterDateTime
     * @return array
     */
    public function timesAndFirstDateTime(string $actionKey, \DateTime $afterDateTime): array;

    /**
     * purge non actual dates
     *
     * @param string $actionKey
     * @param \DateTime|null $beforeDateTime
     * @return void
     */
    public function purge(string $actionKey, \DateTime $beforeDateTime = null): void;
}
