<?php

namespace Websecret\OpeningHoursCalculator;

use Carbon\Carbon;
use Spatie\OpeningHours\OpeningHours;

class OpeningHoursCalculator
{
    /**
     * Helper class for handling working hours
     *
     * @var OpeningHours
     */
    protected $openingHours;

    /**
     * TimingCalculator constructor.
     *
     * @param OpeningHours $openingHours
     */
    public function __construct(OpeningHours $openingHours)
    {
        $this->openingHours = $openingHours;
    }

    /**
     * Get working days between two dates
     *
     * @param Carbon $from
     * @param Carbon $to
     * @return int
     */
    public function workingDaysBetween(Carbon $from, Carbon $to)
    {
        $days = 1;

        for ($current = $from->copy(); $current < $to; $current->addDay()) {
            if (count($this->openingHours->forDate($current)) > 0) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * Get working time between two dates in minutes
     *
     * @param Carbon $from
     * @param Carbon $to
     * @return float|int
     */
    public function workingMinutesBetween(Carbon $from, Carbon $to)
    {
        $minutes = 0;

        for ($current = $from->copy(); $current < $to; $current->addDay()) {
            if (count($this->openingHours->forDate($from)) > 0) {
                $minutes += $this->workingMinutesInDayBetween(
                    $current,
                    $from,
                    $to
                );
            }
        }

        return $minutes;
    }

    /**
     * Get working minutes in exact day
     *
     * @param Carbon $day
     * @param Carbon $from
     * @param Carbon $to
     * @return float|int
     */
    protected function workingMinutesInDayBetween(Carbon $day, Carbon $from, Carbon $to)
    {
        $minutes = 0;

        $timeRanges = $this->openingHours->forDate($day);

        foreach ($timeRanges as $timeRange) {
            $start = new Carbon($timeRange->start()->toDateTime($day->copy()));
            $end   = new Carbon($timeRange->end()->toDateTime($day->copy()));

            if ($from->greaterThanOrEqualTo($end)) {
                continue;
            }

            if ($to->lessThanOrEqualTo($start)) {
                continue;
            }

            $start = ($from->greaterThan($start) && $from->isSameDay($start)) ? $from : $start;
            $end   = ($to->lessThan($end) && $to->isSameDay($end)) ? $to : $end;

            $minutes += $end->diffInMinutes($start);
        }

        return $minutes;
    }

    /**
     * Get working time between two dates in hours
     *
     * @param Carbon $from
     * @param Carbon $to
     * @return float|int
     */
    public function workingHoursBetween(Carbon $from, Carbon $to)
    {
        return $this->workingMinutesBetween($from, $to) / 60;
    }

    /**
     * Get working time between two dates in seconds
     *
     * @param Carbon $from
     * @param Carbon $to
     * @return float|int
     */
    public function workingSecondsBetween(Carbon $from, Carbon $to)
    {
        return $this->workingMinutesBetween($from, $to) * 60;
    }
}