<?php

namespace Efati\ModuleGenerator\Tests\Support;

use Carbon\Carbon;
use Efati\ModuleGenerator\Support\Goli;
use PHPUnit\Framework\TestCase;

class GoliDiffForHumansTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
    }

    public function testPastDifferenceRelativeToNow(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 10, 12, 0, 0));
        $goli = Goli::instance(Carbon::create(2024, 1, 9, 12, 0, 0));

        $this->assertSame('1 روز پیش', $goli->diffForHumans());
    }

    public function testFutureDifferenceWithPersianDigits(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 10, 12, 0, 0));
        $goli = Goli::instance(Carbon::create(2024, 1, 12, 12, 0, 0));

        $this->assertSame('۲ روز بعد', $goli->diffForHumans(null, true));
    }

    public function testDifferenceAgainstAnotherCarbonInstance(): void
    {
        $goli = Goli::instance(Carbon::create(2024, 1, 1, 8, 0, 0));
        $other = Carbon::create(2024, 1, 1, 10, 0, 0);

        $this->assertSame('2 ساعت پیش', $goli->diffForHumans($other));
    }
}
