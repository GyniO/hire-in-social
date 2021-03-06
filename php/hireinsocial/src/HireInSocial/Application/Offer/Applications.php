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

interface Applications
{
    public function alreadyApplied(EmailHash $emailHash, Offer $offer) : bool;
    public function add(Application $application) : void;
}
