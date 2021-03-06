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

namespace HireInSocial\Tests\Application\Unit\Offer;

use HireInSocial\Application\Exception\InvalidAssertionException;
use HireInSocial\Application\Hash\Encoder;
use HireInSocial\Application\Offer\Application\EmailHash;
use PHPUnit\Framework\TestCase;

final class EmailHashTest extends TestCase
{
    public function test_creating_from_raw_eamil()
    {
        $hash = EmailHash::fromRaw('email@example.com', $this->createMD5Encoder());

        $this->assertEquals(
            \md5('email@example.com'),
            $hash->toString()
        );
    }

    public function test_not_accepting_emails_with_plus_tags()
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Email address can\'t contain + character');

        EmailHash::fromRaw('email+tag@example.com', $this->createMock(Encoder::class));
    }

    public function test_removing_dots_from_email_local()
    {
        $this->assertEquals(
            EmailHash::fromRaw('norbert.orzechowicz.test@example.com', $this->createMD5Encoder())->toString(),
            EmailHash::fromRaw('norbertorzechowicztest@example.com', $this->createMD5Encoder())->toString()
        );
    }

    public function createMD5Encoder(): Encoder
    {
        return new class implements Encoder {
            public function encode(string $value): string
            {
                return \md5($value);
            }
        };
    }
}
