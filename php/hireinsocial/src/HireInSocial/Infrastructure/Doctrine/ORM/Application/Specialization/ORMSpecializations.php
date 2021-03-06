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

namespace HireInSocial\Infrastructure\Doctrine\ORM\Application\Specialization;

use Doctrine\ORM\EntityManager;
use HireInSocial\Application\Specialization\Specialization;
use HireInSocial\Application\Specialization\Specializations;

final class ORMSpecializations implements Specializations
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function add(Specialization $specialization): void
    {
        $this->entityManager->persist($specialization);
    }

    public function get(string $slug): Specialization
    {
        return $this->entityManager->getRepository(Specialization::class)->findOneBy(['slug' => $slug]);
    }
}
