<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (22:19)
 */

namespace Makm\FloodControl;

/**
 * Interface FloodAction
 * @package Makm\FloodControl
 */
interface ActionInterface
{
    public const GROUP_DEFAULT = 'default';

    /**
     * @return string
     */
    public function getGroup(): string;

    /**
     * @return string
     */
    public function getActionIdentity(): string;
}
