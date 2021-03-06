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

namespace HireInSocial\Application\Command\Offer;

use HireInSocial\Application\Command\Offer\Apply\Attachment;
use HireInSocial\Application\Exception\Exception;
use HireInSocial\Application\Hash\Encoder;
use HireInSocial\Application\Offer\Application;
use HireInSocial\Application\Offer\Applications;
use HireInSocial\Application\Offer\EmailFormatter;
use HireInSocial\Application\Offer\Offers;
use HireInSocial\Application\System\Calendar;
use HireInSocial\Application\System\Handler;
use HireInSocial\Application\System\Mailer;
use HireInSocial\Application\System\Mailer\Attachments;
use HireInSocial\Application\System\Mailer\Recipients;
use Ramsey\Uuid\Uuid;

final class ApplyThroughEmailHandler implements Handler
{
    private $mailer;
    private $offers;
    private $applications;
    private $encoder;
    private $calendar;
    /**
     * @var EmailFormatter
     */
    private $emailFormatter;

    public function __construct(
        Mailer $mailer,
        Offers $offers,
        Applications $applications,
        Encoder $encoder,
        EmailFormatter $emailFormatter,
        Calendar $calendar
    ) {
        $this->mailer = $mailer;
        $this->offers = $offers;
        $this->applications = $applications;
        $this->encoder = $encoder;
        $this->emailFormatter = $emailFormatter;
        $this->calendar = $calendar;
    }

    public function handles(): string
    {
        return ApplyThroughEmail::class;
    }

    public function __invoke(ApplyThroughEmail $command) : void
    {
        $offer = $this->offers->getById(Uuid::fromString($command->offerId()));
        $emailHash = Application\EmailHash::fromRaw($command->from(), $this->encoder);

        if ($this->applications->alreadyApplied($emailHash, $offer)) {
            throw new Exception('This email address already applied for that job offer');
        }

        $this->mailer->send(
            new Mailer\Email(
                $this->emailFormatter->applicationSubject($command->subject()),
                $this->emailFormatter->applicationBody($command->htmlBody())
            ),
            new Mailer\Sender(
                \sprintf('no-reply@%s', $this->mailer->domain()),
                $this->mailer->domain(),
                $command->from()
            ),
            new Recipients(new Mailer\Recipient($offer->contact()->email(), $offer->contact()->name())),
            new Attachments(
                ...\array_map(
                    function (Attachment $attachment) {
                        return new Mailer\Attachment($attachment->filePath());
                    },
                    $command->attachments()
                )
            )
        );

        $this->applications->add(
            Application::forOffer(
                $emailHash,
                $offer,
                $this->calendar
            )
        );
    }
}
