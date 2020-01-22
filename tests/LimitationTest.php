<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (22:26)
 */

namespace Makm\FloodControl\Tests;


use Makm\FloodControl\Limitations;
use PHPUnit\Framework\TestCase;

/**
 * Class LimitationTest
 * @package Makm\FloodControl\Tests
 */
class LimitationTest extends TestCase
{
    /**
     * @param $expected
     * @param $result
     * @return bool
     */
    public function arrayIsEqualWithOrdering($expected, $result)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($expected, $result))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($expected as $k => $v) {
            if ($v !== $result[$k]) {
                return false;
            }
        }

        // we have identical indexes, and no unequal values
        return true;
    }


    public function testGetLimits(): void
    {
        $limitations = new Limitations(
            [
                ['period' => Limitations::PERIOD_DAY, 'amount' => 1, 'times' => 5],
            ]
        );
        $result = $limitations->getLimits();
        $this->assertEquals(['day' => ['1' => 5]], $result);
        $result = $limitations->getLimits('some-group');
        $this->assertEquals([], $result);
    }


    public function testGetLimitOrderingCheck()
    {
        //check sort
        $limitations = new Limitations(
            [
                ['period' => Limitations::PERIOD_WEEK, 'amount' => 5, 'times' => 15],
                ['period' => Limitations::PERIOD_WEEK, 'amount' => 3, 'times' => 3],
                ['period' => Limitations::PERIOD_DAY, 'amount' => 3, 'times' => 10],
                ['period' => Limitations::PERIOD_DAY, 'amount' => 1, 'times' => 2],
                ['period' => Limitations::PERIOD_MONTH, 'amount' => 1, 'times' => 200],
            ]
        );
        $expected = [
            'day' => ['1' => 2, '3' => 10],
            'week' => ['3' => 3, '5' => 15],
            'month' => ['1' => 200],
        ];
        $result = $limitations->getLimits();
        $this->assertEquals($expected, $result);
        $isEqual = $this->arrayIsEqualWithOrdering($expected, $result);

        $this->assertTrue($isEqual);
    }
}
