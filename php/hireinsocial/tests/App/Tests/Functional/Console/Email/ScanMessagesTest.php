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

namespace App\Tests\Functional\Console\Email;

use App\Command\Email\ScanMessages;
use App\Tests\Functional\Console\ConsoleTestCase;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\Message\EmailAddress;
use Ddeboer\Imap\MessageInterface;
use Ddeboer\Imap\Search\Flag\Unseen;
use Ddeboer\Imap\Test\RawMessageIterator;
use HireInSocial\Application\Query\Offer\ApplicationQuery;
use HireInSocial\Application\Query\Offer\Model\Offer;
use HireInSocial\Application\Query\Offer\OfferFilter;
use HireInSocial\Application\Query\Offer\OfferQuery;
use HireInSocial\Tests\Application\MotherObject\Command\Offer\PostOfferMother;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class ScanMessagesTest extends ConsoleTestCase
{
    public function test_marking_messages_for_existing_offer_as_seen_and_forwarding_email()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $mailbox = $this->createMock(MailboxInterface::class);
        $fs = $this->createMock(Filesystem::class);

        $connection->expects($this->once())
            ->method('getMailbox')
            ->with('INBOX')
            ->willReturn($mailbox);

        $offer = $this->createOffer();

        $command = new ScanMessages(self::$system, $connection, $fs);
        $application = new Application('test');
        $application->add($command);

        $commandTester = new CommandTester($application->find(ScanMessages::NAME));


        $message = $this->createMessage(
            sprintf('apply+%s', $offer->emailHash()),
            'doe',
            'example.com',
            'Jon Doe'
        );

        $mailbox->expects($this->once())
            ->method('getMessages')
            ->with(new Unseen(), \SORTDATE, false)
            ->willReturn(new RawMessageIterator([$message]));


        $commandTester->execute([
            'command'  => ScanMessages::NAME,
        ]);

        $this->assertStringContainsString(
            "New messages: 1",
            $commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            "Message forwarded to offer contact email and marked as seen.",
            $commandTester->getDisplay()
        );
        $this->assertTrue(
            $this->system()->query(ApplicationQuery::class)->alreadyApplied($offer->id()->toString(), 'doe@example.com')
        );
        $this->assertEquals(1, $this->system()->query(ApplicationQuery::class)->countFor($offer->id()->toString()));
    }

    public function test_marking_messages_for_non_existing_offer_as_seen()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $mailbox = $this->createMock(MailboxInterface::class);
        $fs = $this->createMock(Filesystem::class);

        $connection->expects($this->once())
            ->method('getMailbox')
            ->with('INBOX')
            ->willReturn($mailbox);

        $command = new ScanMessages(self::$system, $connection, $fs);
        $application = new Application('test');
        $application->add($command);

        $commandTester = new CommandTester($application->find(ScanMessages::NAME));

        $message = $this->createMessage(
            'apply+non-existing-offer',
            'doe',
            'example.com',
            'Jon Doe'
        );

        $mailbox->expects($this->once())
            ->method('getMessages')
            ->with(new Unseen(), \SORTDATE, false)
            ->willReturn(new RawMessageIterator([$message]));


        $commandTester->execute([
            'command' => ScanMessages::NAME,
        ]);

        $this->assertStringContainsString(
            "New messages: 1",
            $commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            'No active related job offer',
            $commandTester->getDisplay()
        );
    }

    public function createOffer(): Offer
    {
        $user = $this->systemContext->createUser();
        $this->systemContext->createSpecialization('spec');
        $this->systemContext->system()->handle(PostOfferMother::random($user->id(), 'spec'));

        $offer = $this->system()->query(OfferQuery::class)->findAll(OfferFilter::allFor('spec'))->first();

        return $offer;
    }

    public function createMessage(string $sentTo, string $fromMailbox, string $fromHost, string $fromName): MockObject
    {
        $message = $this->createMock(MessageInterface::class);

        $message->method('getBodyHtml')
            ->willReturn('Job Offer Content HTML');
        $message->method('getBodyText')
            ->willReturn('Job Offer Content Text');
        $message->method('getSubject')
            ->willReturn('Job Offer');

        $message->method('getTo')
            ->willReturn([new EmailAddress($sentTo, 'hirein.social', 'Somebody')]);
        $message->method('getSender')
            ->willReturn([new EmailAddress($fromMailbox, $fromHost, $fromName)]);

        return $message;
    }
}
