<?php

declare(strict_types=1);

namespace HireInSocial\Application\Command\Offer;

use HireInSocial\Application\Command\Offer\PostOffer;
use HireInSocial\Application\Facebook\FacebookGroupService;
use HireInSocial\Application\Facebook\Draft;
use HireInSocial\Application\Facebook\Post;
use HireInSocial\Application\Facebook\Posts;
use HireInSocial\Application\Offer\Contact;
use HireInSocial\Application\Offer\Contract;
use HireInSocial\Application\Offer\Offer;
use HireInSocial\Application\Offer\Company;
use HireInSocial\Application\Offer\Description;
use HireInSocial\Application\Offer\Location;
use HireInSocial\Application\Offer\OfferFormatter;
use HireInSocial\Application\Offer\Offers;
use HireInSocial\Application\Offer\Position;
use HireInSocial\Application\Offer\Salary;
use HireInSocial\Application\Offer\Slug;
use HireInSocial\Application\Offer\Slugs;
use HireInSocial\Application\Specialization\Specialization;
use HireInSocial\Application\Specialization\Specializations;
use HireInSocial\Application\System\Calendar;
use HireInSocial\Application\System\Handler;

final class PostOfferHandler implements Handler
{
    private $calendar;
    private $offers;
    private $facebookGroupService;
    private $formatter;
    private $posts;
    private $specializations;
    private $slugs;

    public function __construct(
        Calendar $calendar,
        Offers $offers,
        Posts $posts,
        FacebookGroupService $facebookGroupService,
        OfferFormatter $formatter,
        Specializations $specializations,
        Slugs $slugs
    ) {
        $this->calendar = $calendar;
        $this->facebookGroupService = $facebookGroupService;
        $this->formatter = $formatter;
        $this->offers = $offers;
        $this->posts = $posts;
        $this->specializations = $specializations;
        $this->slugs = $slugs;
    }

    public function handles(): string
    {
        return PostOffer::class;
    }

    public function __invoke(PostOffer $command) : void
    {
        $specialization = $this->specializations->get($command->specialization());

        $offer = $this->createOffer($command, $specialization);

        if ($command->offer()->channels()->facebookGroup()) {
            $draft = new Draft(
                $command->fbUserId(),
                $this->formatter->format(
                    $offer
                ),
                $command->offer()->company()->url()
            );

            $postId = $this->facebookGroupService->postAtGroupAs(
                $draft,
                $specialization->facebookChannel()->group(),
                $specialization->facebookChannel()->page()
            );
            $this->posts->add(new Post($postId, $offer, $draft));
        }

        $this->offers->add($offer);
        $this->slugs->add(Slug::from($offer, $this->calendar));
    }

    private function createOffer(PostOffer $command, Specialization $specialization): Offer
    {
        return new Offer(
            $specialization->id(),
            new Company(
                $command->offer()->company()->name(),
                $command->offer()->company()->url(),
                $command->offer()->company()->description()
            ),
            new Position(
                $command->offer()->position()->name(),
                $command->offer()->position()->description()
            ),
            new Location($command->offer()->location()->remote(), $command->offer()->location()->name()),
            $command->offer()->salary()
                ? new Salary(
                    $command->offer()->salary()->min(),
                    $command->offer()->salary()->max(),
                    $command->offer()->salary()->currencyCode(),
                    $command->offer()->salary()->isNet()
                )
                : null,
            new Contract(
                $command->offer()->contract()->type()
            ),
            new Description(
                $command->offer()->description()->requirements(),
                $command->offer()->description()->benefits()
            ),
            new Contact(
                $command->offer()->contact()->email(),
                $command->offer()->contact()->name(),
                $command->offer()->contact()->phone()
            ),
            $this->calendar
        );
    }
}