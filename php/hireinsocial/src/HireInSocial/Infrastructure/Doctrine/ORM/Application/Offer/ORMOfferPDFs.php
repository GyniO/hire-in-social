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

namespace HireInSocial\Infrastructure\Doctrine\ORM\Application\Offer;

use Doctrine\ORM\EntityManager;
use HireInSocial\Application\Offer\OfferPDF;
use HireInSocial\Application\Offer\OfferPDFs;

final class ORMOfferPDFs implements OfferPDFs
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(OfferPDF $offerPDF): void
    {
        $this->entityManager->persist($offerPDF);
    }
}
