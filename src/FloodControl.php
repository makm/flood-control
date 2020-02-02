<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:50)
 */

namespace Makm\FloodControl;

use Makm\FloodControl\AttemptProvider\AttemptProviderInterface;
use Makm\FloodControl\Exception\IncorrectStateException;

/**
 * Class FloodControl
 * @package MakmLibs\FloodControl
 */
class FloodControl
{
    private Limitations $limitations;

    private AttemptProviderInterface $attemptProvider;

    /**
     * FloodControl constructor.
     * @param AttemptProviderInterface $attemptProvider
     * @param array|Limitations $limitConfig
     */
    public function __construct(AttemptProviderInterface $attemptProvider, $limitConfig)
    {
        $this->attemptProvider = $attemptProvider;
        if ($limitConfig instanceof Limitations) {
            $this->limitations = $limitConfig;
        } elseif (is_array($limitConfig)) {
            $this->limitations = new Limitations($limitConfig);
        } else {
            $this->limitations = new Limitations([]);
        }
    }

    /**
     * @param ActionInterface $floodAction
     * @return string
     */
    private function createKey(ActionInterface $floodAction): string
    {
        return $floodAction->getGroup().'-'.$floodAction->getActionIdentity();
    }

    /**
     * @param ActionInterface $floodAction
     * @param \DateTime|null $dateTime
     * @return bool
     * @throws \Exception
     */
    public function doAttempt(ActionInterface $floodAction, ?\DateTime $dateTime = null): bool
    {
        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        $state = $this->allow($floodAction);
        if (!$state->getAllow()) {
            return false;
        }

        $this->attemptProvider->push(
            $this->createKey($floodAction),
            $dateTime
        );

        [$period, $amount] = \array_values($this->limitations->getExtremeLimit($floodAction->getGroup()));
        $purgeBeforeDate = new \DateTime("-{$amount} {$period}");

        $this->attemptProvider->purge($this->createKey($floodAction), $purgeBeforeDate);

        return true;
    }

    /**
     * @param ActionInterface $floodAction
     * @param \DateTime|null $dateTime
     * @return AttemptState
     * @throws \Exception
     */
    public function allow(ActionInterface $floodAction, ?\DateTime $dateTime = null): AttemptState
    {
        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        $nextAttemptAllowAfterSecondsList = [];

        $limits = $this->limitations->getLimits($floodAction->getGroup());
        foreach ($limits as $period => $periodAmounts) {
            $key = $this->createKey($floodAction);
            foreach ($periodAmounts as $amount => $periodTimes) {
                $tillDate = clone $dateTime;
                $tillDate->modify("-{$amount} {$period}");

                /* @var int $times */
                /* @var \DateTime $firstActionDateTime */
                [$times, $firstActionDateTime] = $this->attemptProvider->timesAndFirstDateTime($key, $tillDate);
                $allowTimes = $periodTimes - $times;

                if ($allowTimes <= 0) {
                    $nextAttemptAllowAfterSecondsList[] =
                        $firstActionDateTime
                            ? $firstActionDateTime->format('U') - $tillDate->format('U')
                            : $dateTime->format('U') - $tillDate->format('U');
                }
            }
        }

        if ($nextAttemptAllowAfterSecondsList) {
            return new AttemptState(false, max($nextAttemptAllowAfterSecondsList));
        }


        return new AttemptState(true, null);
    }
}
