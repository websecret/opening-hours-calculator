<?php

namespace N7olkachev\SimpleDelegator\Test;

use Carbon\Carbon;
use Spatie\OpeningHours\OpeningHours;
use Websecret\OpeningHoursCalculator\OpeningHoursCalculator;
use PHPUnit\Framework\TestCase;

class OpeningHoursCalculatorTest extends TestCase
{
    /** @var OpeningHoursCalculator */
    protected $calculator;

    protected function setUp()
    {
        parent::setUp();

        $this->calculator = new OpeningHoursCalculator(OpeningHours::create([
            'monday'    => ['09:00-12:00', '13:00-18:00'],
            'tuesday'   => ['09:00-12:00', '13:00-18:00'],
            'wednesday' => ['09:00-12:00', ],
            'thursday'  => ['09:00-12:00', '13:00-18:00'],
            'friday'    => ['09:00-12:00', '13:00-16:00', '19:00-21:00'],
            'saturday'  => ['09:00-12:00', '13:00-16:00'],
            'sunday'    => [],
            'exceptions' => [
                '2018-08-02' => ['15:00-16:00'],
            ],
        ]));
    }

    /** @test */
    public function it_works_with_days()
    {
        $sunday    = Carbon::create(2018, 7, 29);
        $monday    = $sunday->copy()->addDay();
        $tuesday   = $monday->copy()->addDay();
        $wednesday = $tuesday->copy()->addDay();

        $this->assertEquals(1, $this->calculator->workingDaysBetween($sunday, $monday));
        $this->assertEquals(2, $this->calculator->workingDaysBetween($monday, $tuesday));
        $this->assertEquals(3, $this->calculator->workingDaysBetween($monday, $wednesday));
    }

    /** @test */
    public function it_works_with_time()
    {
        $x = Carbon::create(2018, 7, 30, 8, 0, 0);    // Monday    08:00
        $y = Carbon::create(2018, 7, 30, 17, 0, 0);   // Monday    17:00
        $time1 = $x->copy()->addHours(3);             // Monday    11:00
        $time2 = $time1->copy()->addHours(3);         // Monday    14:00
        $time3 = $x->copy()->addDay();                // Tuesday   08:00
        $time4 = $x->copy()->addDay(2)->addHours(10); // Wednesday 18:00
        $time5 = $x->copy()->addDay(3)
            ->addHours(7)->addMinutes(30);            // Thursday  15:30 2018-08-02 !!!Exceptional day

        $this->assertEquals(2, $this->calculator->workingHoursBetween($x, $time1));
        $this->assertEquals(4, $this->calculator->workingHoursBetween($x, $time2));
        $this->assertEquals(8, $this->calculator->workingHoursBetween($x, $time3));
        $this->assertEquals(8 + 8 + 3, $this->calculator->workingHoursBetween($x, $time4));
        $this->assertEquals(8 * 60 + 8 * 60 + 3 * 60 + 30, $this->calculator->workingMinutesBetween($x, $time5));
        $this->assertEquals(1, $this->calculator->workingHoursBetween($y, $time3));

        $this->assertEquals(6, $this->calculator->workingHoursBetween(
            Carbon::create(2018, 8, 3, 10, 0, 0), // Friday 10:00
            Carbon::create(2018, 8, 3, 20, 0, 0)  // Friday 20:00
        ));
    }
}
