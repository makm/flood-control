<?php


namespace Makm\FloodControl;

/**
 * Class AttemptState
 * @package Makm\FloodControl
 */
class AttemptState
{
    /**
     * @var bool
     */
    private bool $allow;

    /**
     * @var integer|null
     */
    private ?int $nextAllowAfterSeconds;

    /**
     * AttemptState constructor.
     * @param bool $allow
     * @param int|null $nextAllowAfterSeconds
     */
    public function __construct(bool $allow, ?int $nextAllowAfterSeconds)
    {
        $this->allow = $allow;
        $this->nextAllowAfterSeconds = $nextAllowAfterSeconds;
    }

    /**
     * @return bool
     */
    public function getAllow(): bool
    {
        return $this->allow;
    }

    /**
     * @return int|null
     */
    public function getNextAllowAfterSeconds(): ?int
    {
        return $this->nextAllowAfterSeconds;
    }
}
