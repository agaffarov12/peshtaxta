<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\CampaignView;
use App\Entity\Client;
use Campaign\Campaign;
use Campaign\CampaignId;
use Campaign\CampaignStatus;
use Campaign\Order;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Booking;
use Product\Product;
use Product\ProductId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CampaignRepository {
    public function __construct(private readonly EntityManagerInterface $orm)
    {
    }

    public function add(Campaign $campaign): void
    {
        $this->orm->persist($campaign);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function findById(ProductId | string $id): ?Campaign
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = CampaignId::fromString($id);
        }

        $campaign = $this->orm->find(Campaign::class, $id);

        if ($campaign === null) {
            throw new EntityNotFoundException();
        }

        return $campaign;
    }

    public function findWithIds(array $ids): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("c")
            ->from(Campaign::class, "c")
            ->where($builder->expr()->in("c.id", ":ids"))
            ->setParameter("ids", $ids);

        return $builder->getQuery()->getResult();
    }

    public function list(
        int    $offset = 0,
        int    $limit = 10,
        string $productId = null,
        string $clientId = null,
        string $orderId = null,
        string $status = null,
        string $clientFistName = null,
        string $clientLastName = null,
        string $productName = null,
        bool   $nonClosed = false
    ): Paginator
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->addSelect(
                sprintf(
                    'NEW %s(
                        c.id,
                        c.price.amount,
                        c.status,
                        b.id,
                        b.status,
                        b.startDate,
                        b.endDate,
                        pl.id,
                        pl.name,
                        pl.price.amount,
                        client.id,
                        client.firstName,
                        client.lastName,
                        client.type,
                        p.id,
                        p.name,
			            c.orderId
                    )',
                    CampaignView::class
                )
            )
            ->from(Campaign::class, "c")
            ->where($builder->expr()->eq("c.deleted", ":deleted"))
            ->setParameter("deleted", false);

        if ($productId && trim($productId)) {
            $builder->andWhere($builder->expr()->eq("c.productId", ":productId"))
                ->setParameter("productId", $productId);
        }

        if ($clientId && trim($clientId)) {
            $builder->andWhere($builder->expr()->eq("c.clientId", ":clientId"))
                ->setParameter("clientId", $clientId);
        }

        if ($orderId && trim($orderId)) {
            $builder->andWhere($builder->expr()->eq("c.orderId", ":orderId"))
                ->setParameter("orderId", $orderId);
        }

        if ($status && trim($status)) {
            $builder->andWhere($builder->expr()->eq("c.status", ":status"))
                ->setParameter("status", $status);
        }

        if ($nonClosed) {
            $statuses = [
                CampaignStatus::ACTIVE->value,
                CampaignStatus::CREATED->value,
                CampaignStatus::RESERVED->value
            ];

            $builder->andWhere($builder->expr()->in("c.status", ":statuses"))
                ->setParameter("statuses", $statuses);
        }

        $builder->innerJoin(Client::class, "client", "WITH", "client.id = c.clientId");

        if ($clientFistName && trim($clientFistName)) {
            $builder->andWhere(
                $builder->expr()->eq("client.firstName", ":fName")
            )->setParameter("fName", $clientFistName);
        }

        if ($clientLastName && trim($clientLastName)) {
            $builder->andWhere(
                $builder->expr()->eq("client.lastName", ":lName")
            )->setParameter("lName", $clientLastName);
        }

        $builder->innerJoin(Product::class, "p", "WITH", "p.id = c.productId");

        if ($productName && trim($productName)) {
            $builder->andWhere(
                $builder->expr()->eq("p.name", ":productName")
            )->setParameter("productName", $productName);
        }

        $builder
            ->innerJoin(Booking::class, "b", "WITH", "b.id = c.bookingId")
            ->innerJoin("b.placement", "pl");

        $query = $builder->setFirstResult($offset)->setMaxResults($limit)->getQuery();

        return new Paginator($query);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getCampaignDetails(string | CampaignId $id): ?CampaignView
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = CampaignId::fromString($id);
        }

        $builder = $this->orm->createQueryBuilder();

        $builder
            ->addSelect(sprintf(
                'NEW %s(
                    c.id,
                    c.price.amount,
                    c.status,
                    b.id,
                    b.status,
                    b.startDate,
                    b.endDate,
                    pl.id,
                    pl.name,
                    pl.price.amount,
                    client.id,
                    client.firstName,
                    client.lastName,
                    client.type,
                    p.id,
                    p.name,
                    c.orderId,
                    file.name,
                    file.id,
                    creative.mounted
                )',
                CampaignView::class
            ))
            ->from(Campaign::class, "c")
            ->where($builder->expr()->eq("c.id", ":id"))
            ->setParameter("id", $id)
            ->innerJoin(Booking::class, "b", "WITH", "b.id = c.bookingId")
            ->innerJoin("b.placement", "pl")
            ->innerJoin(Client::class, "client", "WITH", "client.id = c.clientId")
            ->innerJoin(Product::class, "p", "WITH", "p.id = c.productId")
            ->innerJoin("c.creative", "creative")
            ->innerJoin("creative.file", "file");

        try {
            return $builder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new EntityNotFoundException();
        }
    }

    public function getCampaignsBetween(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        string $productId,
        string $placementId
    ): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("c")
            ->from(Campaign::class, "c")
            ->where($builder->expr()->eq("c.productId", ":productId"))
            ->andWhere($builder->expr()->eq("c.deleted", ":deleted"))
            ->setParameter("deleted", false)
            ->setParameter("productId", $productId)
            ->innerJoin(Booking::class, "b", "WITH", "b.id = c.bookingId")
            ->andWhere($builder->expr()->eq("IDENTITY(b.placement)", ":placementId"))->setParameter("placementId", $placementId)
            ->andWhere($builder->expr()->lt("b.startDate", ":endDate"))->setParameter("endDate", $endDate)
            ->andWhere($builder->expr()->gt("b.endDate", ":startDate"))->setParameter("startDate", $startDate);

        return $builder->getQuery()->getResult();    
    }

    public function getNumberOfCreatedCampaignsBetween(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): int
    {
        $builder = $this->orm->createQueryBuilder(Campaign::class);

        $builder
            ->select($builder->expr()->count("c.id"))
            ->from(Campaign::class, "c");

        if ($startDate && $endDate) {
            $builder
                ->where($builder->expr()->between("c.createdAt", ":start", ":end"))
                ->setParameter("start", $startDate)
                ->setParameter("end", $endDate);
        }

        return $builder->getQuery()->getSingleScalarResult();
    }

    public function getCampaignsWithOrder(string $orderId): array
    {
        $builder = $this->orm->createQueryBuilder();

        $query = $builder
            ->select("c", "b.startDate", "b.endDate")
            ->from(Campaign::class, "c")
            ->where($builder->expr()->eq("c.orderId", ":orderId"))
            ->andWhere($builder->expr()->eq("c.deleted", ":deleted"))
            ->setParameter("deleted", false)
            ->setParameter("orderId", $orderId)
            ->innerjoin(Booking::class, "b", "WITH", "b.id = c.bookingId");

        return $query->getQuery()->getResult();
    }

    public function getCampaignsOfClient(UuidInterface $clientId)
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["c.price.amount, SUM(p.price.amount)"])
            ->from(Campaign::class, "c")
            ->where($builder->expr()->eq("c.clientId", ":id"))
            ->setParameter("id", $clientId)
            ->leftJoin(Order::class, "o", Expr\Join::WITH, "o.id = c.orderId")
            ->leftJoin('o.payments', 'p')
            ->groupBy("c.id");

        return $builder->getQuery()->getResult();
    }
}
