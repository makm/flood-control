<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (22:26)
 */

namespace Makm\FloodControl\Tests;


use Makm\FloodControl\ActionInterface;
use Makm\FloodControl\AttemptProvider\AttemptProviderInterface;
use Makm\FloodControl\FloodControl;
use Makm\FloodControl\Limitations;
use PHPUnit\Framework\TestCase;

/**
 * Class FloodControlTest
 * @package Makm\FloodControl\Tests
 */
class FloodControlTest extends TestCase
{
    private FloodControl $floodControl;
    private AttemptProviderInterface $provider;
    private Limitations $limitations;

    /**
     * @return void
     */
    public function setUp(): void
    {
        /** @var AttemptProviderInterface provider */
        $this->provider = $this->createMock(AttemptProviderInterface::class);
        $this->limitations = $this->createMock(Limitations::class);
        $this->floodControl = new FloodControl($this->provider, $this->limitations);
    }

    /**
     * @param $name
     * @return ActionInterface
     */
    private function getAction($name): ActionInterface
    {
        $action = $this->createMock(ActionInterface::class);
        $action->method('getGroup')->willReturn('test-group');
        $action->method('getActionIdentity')->willReturn($name);

        return $action;
    }

    /**
     * @throws \Exception
     */
    public function testDoAttempt()
    {
        $action = $this->getAction('test-action');
        $this->limitations->expects($this->exactly(2))->method('getLimits')->willReturn([
            'day' => [1 => 1],
        ]);
        $this->provider->expects($this->once())->method('push');
        $this->provider->expects($this->exactly(2))->method('times')->willReturn(0,1);
        $result = $this->floodControl->doAttempt($action);
        $this->assertTrue($result);
        $result = $this->floodControl->doAttempt($action);
        $this->assertFalse($result);
    }
}
