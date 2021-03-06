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

namespace HireInSocial\Application\Offer;

use HireInSocial\Application\Offer\Application\EmailHash;
use HireInSocial\Application\System\Calendar;
use Ramsey\Uuid\Uuid;

class Application
{
    private $id;
    private $offerId;
    private $emailHash;
    private $createdAt;

    private function __construct()
    {
    }

    public static function forOffer(EmailHash $email, Offer $offer, Calendar $calendar) : self
    {
        $application = new self();
        $application->id = (string) Uuid::uuid4();
        $application->offerId = (string) $offer->id();
        $application->emailHash = $email->toString();
        $application->createdAt = $calendar->currentTime();

        return $application;
    }
}
