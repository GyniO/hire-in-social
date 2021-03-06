<?php

declare(strict_types=1);

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Tests\Application\Double\Stub;

use HireInSocial\Application\System\Calendar;

class CalendarStub implements Calendar
{
    /**
     * @var \DateTimeImmutable
     */
    private $currentTime;

    public function __construct(\DateTimeImmutable $currentTime)
    {
        $this->currentTime = $currentTime;
    }

    public function currentTime(): \DateTimeImmutable
    {
        return $this->currentTime;
    }

    public function goBack(int $seconds) : void
    {
        $this->currentTime = $this->currentTime->modify(sprintf('-%d seconds', $seconds));
    }
}
