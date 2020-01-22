<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl;

/**
 * Class Limitations
 * @package Makm\FloodControl
 */
class Limitations
{
    private array $limits = [];
    public const PERIOD_MINUTE = 'minute';
    public const PERIOD_HOUR = 'hour';
    public const PERIOD_DAY = 'day';
    public const PERIOD_WEEK = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_YEAR = 'year';

    private const PRIORITY = [
        self::PERIOD_MINUTE,
        self::PERIOD_HOUR,
        self::PERIOD_DAY,
        self::PERIOD_WEEK,
        self::PERIOD_MONTH,
        self::PERIOD_YEAR,
    ];

    /**
     * Limitations constructor.
     * @param array $limitsConfig
     */
    public function __construct($limitsConfig = [])
    {
        foreach ($limitsConfig as $limit) {
            $this->add(
                $limit['period'],
                $limit['amount'],
                $limit['times'],
                $limit['group'] ?? ActionInterface::GROUP_DEFAULT
            );
        }
    }

    /**
     * @return void
     */
    private function sortData(): void
    {
        foreach ($this->limits as &$limitGroup) {
            uksort(
                $limitGroup,
                static function (string $a, string $b) {
                    return array_search($a, self::PRIORITY, true) -
                        array_search($b, self::PRIORITY, true);
                });


            foreach ($limitGroup as &$limitPeriod) {
                ksort($limitPeriod);
            }
        }
    }

    /**
     * @param string $period
     * @param int $amount
     * @param int $times
     * @param string $group
     * @return Limitations
     */
    public function add(string $period, int $amount, int $times, string $group = ActionInterface::GROUP_DEFAULT): self
    {
        $this->limits[$group][$period][$amount] = $times;
        $this->sortData();

        return $this;
    }

    /**
     * return limit data of concrete group as
     * example : ['week'=>['1'=>4]]
     * 4 time on 4 weeks
     *
     * @param string $group
     * @return array
     */
    public function getLimits($group = ActionInterface::GROUP_DEFAULT): array
    {
        return $this->limits[$group] ?? [];
    }

    /**
     * @param string $group
     * @return array
     */
    public function getExtremeLimit($group = ActionInterface::GROUP_DEFAULT): array
    {
        $lastPeriod = array_key_last($this->limits[$group]);
        $lastAmount = array_key_last($this->limits[$group][$lastPeriod]);

        return ['period' => $lastPeriod, 'amount' => $lastAmount];
    }
}
