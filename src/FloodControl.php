<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:50)
 */

namespace Makm\FloodControl;

use Makm\FloodControl\AttemptProvider\AttemptProviderInterface;

/**
 * Class FloodControl
 * @package MakmLibs\FloodControl
 */
class FloodControl
{
    /**
     * @var Limitations
     */
    private $limitations;

    /**
     * @var AttemptProviderInterface
     */
    private $attemptProvider;

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
    private function createKey(ActionInterface $floodAction)
    {
        return $floodAction->getGroup().'-'.$floodAction->getActionIdentity();
    }

    /**
     * @param ActionInterface $floodAction
     * @return bool
     * @throws \Exception
     */
    public function doAttempt(ActionInterface $floodAction): bool
    {
        if (!$this->allow($floodAction)) {
            return false;
        }

        $this->attemptProvider->push(
            $this->createKey($floodAction),
            new \DateTime()
        );

        return true;
    }

    /**
     * @param ActionInterface $floodAction
     * @return bool
     * @throws \Exception
     */
    public function allow(ActionInterface $floodAction): bool
    {
        $limits = $this->limitations->getLimits($floodAction->getGroup());
        foreach ($limits as $period => $periodAmounts) {
            $key = $this->createKey($floodAction);
            foreach ($periodAmounts as $amount => $periodTimes) {
                $tillDate = new \DateTime("-{$amount} {$period}");
                $times = $this->attemptProvider->times($key, $tillDate);
                if ($times < $periodTimes) {
                    return true;
                }
            }
        }

        return false;
    }
}
