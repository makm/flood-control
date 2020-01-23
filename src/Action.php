<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (22:19)
 */

namespace Makm\FloodControl;

/**
 * Class Action
 * @package Makm\FloodControl
 */
class Action implements ActionInterface
{
    private string $group;
    private string $actionIdentity;

    /**
     * Action constructor.
     * @param string $group
     * @param string $actionIdentity
     */
    public function __construct(string $group, string $actionIdentity)
    {
        $this->group = $group;
        $this->actionIdentity = $actionIdentity;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getActionIdentity(): string
    {
        return $this->actionIdentity;
    }
}
