<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (22:26)
 */

namespace Makm\FloodControl\Tests;


use Makm\FloodControl\ActionInterface;
use Makm\FloodControl\AttemptProvider\AttemptProviderInterface;
use Makm\FloodControl\AttemptState;
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
     * @return array
     * @throws \Exception
     */
    public function allowCasesProvider()
    {
        return
            [
                [
                    ['day' => [1 => 1]],
                    [[1, $firstActualDateTime = new \DateTime()]],
                    false,
                    $firstActualDateTime->format('U') - (clone $firstActualDateTime)->modify('-1 day')->format('U'),
                ],
                [
                    ['day' => [1 => 2],],
                    [[1, new \DateTime()]],
                    true,
                    null,
                ],
                [
                    ['month' => [1 => 2]],
                    [[1, new \DateTime()]],
                    true,
                    null,
                ],
                [
                    ['day' => [1 => 4], 'week' => [1 => 10]],
                    [[3, new \DateTime('-20 hour')], [4, new \DateTime('-1 day')]],
                    true,
                    null
                ],
                [
                    ['day' => [1 => 4], 'week' => [1 => 10]],
                    [[4, new \DateTime('-20 hour')], [10, $firstActualDateTime = new \DateTime('-1 day')]],
                    false,
                    $firstActualDateTime->format('U') - (clone $firstActualDateTime)->modify('-6 days')->format('U'),
                ],
                [
                    ['minute' => [1 => 1],'day' => [1 => 4], 'week' => [1 => 10]],
                    [[1, new \DateTime('-10 second')], [4, new \DateTime('-20 hour')], [10, $firstActualDateTime =  new \DateTime('-1 day')]],
                    false,
                    $firstActualDateTime->format('U') - (clone $firstActualDateTime)->modify('-6 days')->format('U'),
                ],
            ];
    }

    /**
     * @dataProvider allowCasesProvider
     * @param $limits
     * @param $timesAndFirstDateTime
     * @param $allow
     * @param $diffSeconds
     * @throws \Exception
     */
    public function testAllowCases($limits, $timesAndFirstDateTime, $allow, $diffSeconds): void
    {
        $action = $this->getAction('test-allow-');
        $this->limitations->expects($this->exactly(1))
            ->method('getLimits')->willReturn($limits);

        $this->provider->expects($this->exactly(count($timesAndFirstDateTime)))
            ->method('timesAndFirstDateTime')
            ->willReturn(...$timesAndFirstDateTime);

        $checkForDateTime = new \DateTime();
        $state = $this->floodControl->allow($action, $checkForDateTime);
        $this->assertEquals($allow, $state->getAllow(), 'allow times:');
        $this->assertEquals($diffSeconds, $state->getNextAllowAfterSeconds());
    }

    /**
     * @dataProvider allowCasesProvider
     *
     * @param $limits
     * @param $timesAndFirstDateTime
     * @param $allow
     * @param $diffSeconds
     * @throws \Exception
     */
    public function testDoAttempt($limits, $timesAndFirstDateTime, $allow, $diffSeconds)
    {
        $action = $this->getAction('test-action');
        $this->limitations->expects($this->exactly(1))->method('getLimits')
            ->willReturn($limits);
        $this->provider->expects($this->exactly(count($timesAndFirstDateTime)))
            ->method('timesAndFirstDateTime')
            ->willReturn(...$timesAndFirstDateTime);


        // purge if allow
        $this->limitations->expects($this->exactly((int) $allow))
            ->method('getExtremeLimit')
            ->willReturn(['period' => 'day', 'amount' => 1]);

        $this->provider->expects($this->exactly((int) $allow))->method('push');
        $this->provider->expects($this->exactly((int) $allow))->method('purge');

        $state = $this->floodControl->doAttempt($action);
        $this->assertEquals($allow, $state);
    }
}
