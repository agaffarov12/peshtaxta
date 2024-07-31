<?php
declare(strict_types=1);

use App\Entity\Insight;
use App\Entity\InsightContext;
use App\Entity\InsightEventType;
use App\Service\InsightsService;
use App\Service\OrderRepository;
use App\Service\OrdersService;
use App\Service\ProductsService;
use Campaign\Campaign;
use Campaign\CampaignStatus;
use Product\Booking;
use Product\PlacementStatus;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require 'config/container.php';

function check(): void
{
    global $container;

    /** @var \Doctrine\ORM\EntityManager $orm */
    $orm = $container->get("doctrine.entity_manager.orm_default");

    $offset    = 0;
    $maxResult = 50;

    $builder = $orm->createQueryBuilder()->setCacheable(false)->setFirstResult($offset)->setMaxResults($maxResult);

    $query = $builder
        ->select("c", "b.startDate", "b.endDate")
        ->from(Campaign::class, "c")
        ->innerjoin(Booking::class, "b", "WITH", "b.id = c.bookingId");

    while ($result = $query->getQuery()->getResult()) {

        foreach ($result as $r) {
            checkStatus($r[0], $r['startDate'], $r['endDate']);
        }

        $orm->flush();

        $query->setFirstResult(++$offset * $maxResult);
    }
}

/**
 * @throws \Psr\Container\NotFoundExceptionInterface
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Doctrine\ORM\EntityNotFoundException
 */
function checkStatus(Campaign &$campaign, DateTimeImmutable $start, DateTimeImmutable $end): void
{
    global $container;

    /** @var ProductsService $service */
    $service = $container->get(ProductsService::class);

    if (
        $campaign->getStatus() === CampaignStatus::CREATED ||
        $campaign->getStatus() === CampaignStatus::CLOSED ||
        $campaign->getStatus() === CampaignStatus::CANCELLED ||
        $campaign->getStatus() === CampaignStatus::BOOKING_CANCELLED
    ) {
        return;
    }

    $now       = new DateTimeImmutable();
    $isPaidFor = isCampaignPaidFor($campaign);
    $started   = $now >= $start && $now < $end;

    if ($started) {
        startCampaign($campaign);

        try {
            $service->LowerPriority($campaign->getProductId(), (string)$campaign->getBookingId());
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            //throw exception
        }
    }

    if ($now >= $end) {
        endCampaign($campaign);
    }

    createInsight($campaign, $campaign->getStatus(), $started, $isPaidFor);
}

/**
 * @throws \Psr\Container\NotFoundExceptionInterface
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Doctrine\ORM\EntityNotFoundException
 */
function startCampaign(Campaign &$campaign) {
    global $container;

    /** @var ProductsService $service */
    $service = $container->get(ProductsService::class);

    $campaign->setStatus(CampaignStatus::ACTIVE);

    $service->changePlacementStatus(
        (string) $campaign->getProductId(),
        (string) $campaign->getCreative()->getProductPlacementId(),
        PlacementStatus::OCCUPIED
    );
}

/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 * @throws \Doctrine\ORM\EntityNotFoundException
 */
function endCampaign(Campaign &$campaign): void {
    global $container;

    /** @var ProductsService $service */
    $service = $container->get(ProductsService::class);

    $campaign->setStatus(CampaignStatus::CLOSED);

    $service->changePlacementStatus(
        (string) $campaign->getProductId(),
        (string) $campaign->getCreative()->getProductPlacementId(),
        PlacementStatus::VACANT
    );
}


function createInsight(Campaign $campaign, CampaignStatus $status, bool $started, bool $isPaidFor): void
{
    global $container;

    /** @var InsightsService $service */
    $service = $container->get(InsightsService::class);

    if ($started && !$isPaidFor) {
        $service->add(
            new Insight(
                "unpaidStarted",
                (string)$campaign->getId(),
                InsightEventType::UNPAID_CAMPAIGN_START,
                InsightContext::CAMPAIGN,
                ['id' => $campaign->getId()]
            )
        );
    }

    if (!$campaign->getCreative()->isMounted() && $status === CampaignStatus::ACTIVE) {
        $service->add(
            new Insight(
                "mountCreative",
                (string)$campaign->getId(),
                InsightEventType::CAMPAIGN_START,
                InsightContext::CAMPAIGN,
                ['id' => $campaign->getId()]
            )
        );
    }

    if ($campaign->getCreative()->isMounted() && $status === CampaignStatus::CLOSED) {
        $service->add(
            new Insight(
                "unmountCreative",
                (string)$campaign->getId(),
                InsightEventType::CAMPAIGN_END,
                InsightContext::CAMPAIGN,
                ['id' => $campaign->getId()]
            )
        );
    }
}

function isCampaignPaidFor(Campaign $campaign): bool
{
    if ($campaign->getOrderId() === null) {
        return false;
    }

    global $container;

    /** @var OrderRepository $repository */
    $repository = $container->get(OrderRepository::class);

    $order = $repository->findById($campaign->getOrderId());

    return $order->isPaid();
}

check();
