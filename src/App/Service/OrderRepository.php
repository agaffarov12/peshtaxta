<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\OrderView;
use App\Entity\Client;
use App\Entity\StatisticsInterval;
use App\Utils\DateIntervalUtil;
use Campaign\Order;
use Campaign\OrderId;
use Campaign\Payment;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Booking;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class OrderRepository
{
    public function __construct(private readonly EntityManagerInterface $orm)
    {
    }

    public function save(Order $order): void
    {
        $this->orm->persist($order);
        $this->orm->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws EntityNotFoundException
     */
    public function getOrderDetails(OrderId | string $id): array
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = OrderId::fromString($id);
        }

        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["o as order", "c.id as clientId", "c.firstName", "c.lastName", "c.type"])
            ->from(Order::class, "o")
            ->where($builder->expr()->eq("o.id", ":id"))
            ->setParameter("id", $id)
            ->innerJoin(Client::class, "c", "WITH", "c.id = o.clientId");

        $result = $builder->getQuery()->getOneOrNullResult();

        if ($result === null) {
            throw new EntityNotFoundException();
        }

        return $result;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function findById(OrderId | string $id): ?Order
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = OrderId::fromString($id);
        }

        $order = $this->orm->find(Order::class, $id);

        if ($order === null) {
            throw new EntityNotFoundException();
        }

        return $order;
    }

    public function list(
        int $offset = 0,
        int $limit = 10,
        ?string $clientFirstName = null,
        ?string $clientLastName = null,
        ?int $minPrice = null,
        ?int $maxPrice = null,
        ?bool $fullyPaid = null
    ): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->addSelect(
                sprintf(
                    'NEW %s(
                        o.id,
                        c.id,
                        c.type,
                        c.firstName,
                        c.lastName,
                        o.createdAt,
                        o.price.amount,
                        SUM(p.price.amount)
                    )',
                    OrderView::class
                )
            )
            ->from(Order::class, 'o');


        $builder->leftJoin("o.payments", "p", 'WITH', 'o.id = IDENTITY(p.order)');
        $builder->innerJoin(Client::class, "c", "WITH", "c.id = o.clientId");

        if ($minPrice)  {
            $builder
                ->andWhere($builder->expr()->gte("o.price.amount", ":minPrice"))
                ->setParameter("minPrice", $minPrice);
        }

        if ($maxPrice)  {
            $builder
                ->andWhere($builder->expr()->lte("o.price.amount", ":maxPrice"))
                ->setParameter("maxPrice", $maxPrice);
        }

        if ($clientFirstName && trim($clientFirstName)) {
            $builder
                ->andWhere($builder->expr()->eq("c.firstName", ":firstName"))
                ->setParameter("firstName", $clientFirstName);
        }

        if ($clientLastName && trim($clientLastName)) {
            $builder
                ->andWhere($builder->expr()->eq("c.lastName", ":lastName"))
                ->setParameter("lastName", $clientLastName);
        }

        $builder->addGroupBy("o.id");
        $builder->addGroupBy("c");
        $builder->orderBy("o.createdAt", "DESC");

        $query     = $builder->setFirstResult($offset)->setMaxResults($limit)->getQuery();

        $paginator = new Paginator($query);
        $paginator->setUseOutputWalkers(false);

        $data = iterator_to_array($paginator);

        /** @var OrderView $orderView */
        //foreach ($data as $orderView) {
        //    $orderView->balanceForAllTime = ($orderView->paidPrice ?? 0) - $orderView->price;
        //    $orderView->balanceForToday = $orderView->paidPrice - abs($orderView->balanceForToday);
        //}

        if ($fullyPaid) {
            $data = array_filter($data, fn(OrderView $orderView) => $orderView->paidPrice >= $orderView->price);
            $data = array_values($data);
        }

        return ['data' => $data, 'count' => $paginator->count()];
    }

    public function getAverageOfPrices(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select('AVG(o.price.amount)')
            ->from(Order::class, 'o');

        if ($startDate !== null && $endDate !== null) {
            $builder
                ->where($builder->expr()->between("o.createdAt", ":start", ":end"))
                ->setParameter("start", $startDate)
                ->setParameter("end", $endDate);
        }

        return $builder->getQuery()->getResult();
    }

    public function getNumberOfCreatedOrders(StatisticsInterval $interval): int
    {
        $date = new DateTimeImmutable();
        
        $dateInterval = DateIntervalUtil::getIntervalObject($interval);

        $date = $date->sub($dateInterval);

        $builder = $this->orm->createQueryBuilder(Order::class);

        $count = $builder->select($builder->expr()->count("o.id"))
                    ->from(Order::class, "o")
                    ->where($builder->expr()->gte("o.createdAt", ":date"))
                    ->setParameter("date", $date)
                    ->getQuery()
                    ->getSingleScalarResult();
         
        return $count;
    }

    public function getOrdersOfClient(UuidInterface $clientId, int $offset = 0, int $limit = 10): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["o as order, SUM(p.price.amount) AS paid"])
            ->from(Order::class, "o")
            ->where($builder->expr()->eq("o.clientId", ":id"))
            ->andWhere($builder->expr()->gt("o.price.amount", ":amount"))
            ->setParameter("id", $clientId)
            ->setParameter("amount", 0)
            ->leftJoin(Payment::class, "p", 'WITH', 'o.id = IDENTITY(p.order)')
            ->groupBy("o.id")
            ->indexBy("o", "o.id");

        $allOrdersSum = $this->calculateOAllOrdersSum2(array_map(fn(array $order) => (string) $order['order']->getId(), $builder->getQuery()->getResult()));
        $paginator = new Paginator($builder->setFirstResult($offset)->setMaxResults($limit)->getQuery());
        $result = iterator_to_array($paginator);
        $ids = array_map(fn(array $order) => (string) $order['order']->getId(), $result);
        $debtSoFar = $this->calculateSoFar($ids);

        $resultArray = [];
        $resultArray['allOrdersSum'] = $allOrdersSum;
        $resultArray['data'] = [];
        foreach($result as $key => $value) {
            if (!isset($debtSoFar[$key])) {
                continue;
            }
            $value['paidSoFar'] = $value['paid'] - abs($debtSoFar[$key]['soFar']);
            $value['paid'] = ($value['paid'] ?? 0) - $value['order']->getPrice()->getAmount();
            $resultArray['data'][] = $value;
        }

        $resultArray['count'] = $paginator->count();

        return $resultArray;
    }

    private function calculateSoFar(array $ids)
    {
        if(empty($ids)) {
            return [];
        }

        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(
                "o.id AS id,
                 SUM(
                    DATE_DIFF(CASE WHEN b.startDate > :date THEN :date ELSE b.startDate END, CASE WHEN b.endDate < :date THEN b.endDate ELSE :date END) * pl.price.amount
                 ) as soFar"
            )
            ->setParameter('date', new DateTimeImmutable())
            ->from(Order::class, "o")
            ->where($builder->expr()->in("o.id", $ids))
            ->innerJoin("o.campaigns", "c", Expr\Join::WITH, $builder->expr()->not($builder->expr()->eq("c.deleted", ":deleted")))->setParameter("deleted", true)
            ->innerJoin(Booking::class, "b", 'WITH', "b.id = c.bookingId")
            ->innerJoin("b.placement", "pl")
            ->groupBy("o.id")
            ->indexBy("o", "o.id");

        return $builder->getQuery()->getResult();
    }

    private function calculateOAllOrdersSum2(array $ids): array
    {
        if(empty($ids)) {
            return ['overall' => 0, 'today' => 0];
        }

        $idsString = '(';

        foreach ($ids as $id) {
            $idsString .= "'{$id}',";
        }
        $idsString = substr($idsString, 0, -1);

        $idsString .= ')';

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult("id", "id");
        $rsm->addScalarResult("amount", "amount");
        $rsm->addScalarResult("payments", "payments");
        $rsm->addScalarResult("sofar", "soFar");

        $sql = "SELECT o.id, o.price_amount AS amount, payments.payment AS payments, psf.sumSoFar AS soFar FROM orders o
        LEFT JOIN (SELECT p.order_id, SUM(p.price_amount) AS payment FROM Payments p GROUP BY order_id) AS payments ON o.id = payments.order_id
        LEFT JOIN (SELECT c.order_id AS cmp, c.deleted AS deleted, SUM(EXTRACT(DAY FROM (CASE WHEN b.start_date > CURRENT_TIMESTAMP THEN CURRENT_TIMESTAMP ELSE b.start_date END ) - (CASE WHEN b.end_date < CURRENT_TIMESTAMP THEN b.end_date ELSE CURRENT_TIMESTAMP END)) * pl.price_amount ) AS sumSoFar FROM product_placement pl INNER JOIN bookings b ON b.placement_id = pl.id INNER JOIN campaigns c ON c.booking_id = b.id GROUP BY c.id) AS psf ON psf.cmp = o.id
        WHERE o.id in {$idsString} AND psf.deleted != true GROUP BY o.id,payments.payment, psf.sumSoFar";

        $query = $this->orm->createNativeQuery($sql, $rsm);

        $result = $query->getResult();
        $returnArr = ['overall' => 0, 'today' => 0];

        foreach($result as $r) {
            $returnArr['overall'] += (($r['payments'] ?? 0) - $r['amount']);
            $returnArr['today'] += (($r['payments'] ?? 0) - abs((int) $r['soFar']));
        }

        return $returnArr;
    }
}

//'NEW %s(
//                        o.id,
//                        c.firstName,
//                        c.lastName,
//                        o.createdAt,
//                        o.price.amount,
//                        SUM(p.price.amount),
//                        SUM(
//                            DATE_DIFF(CASE WHEN b.startDate > :date THEN :date ELSE b.startDate END, CASE WHEN b.endDate < :date THEN b.endDate ELSE :date END) * pl.price.amount
//                        )
//                    )',


//SELECT o.id, o.price_amount AS amount, payments.payment AS payments, psf.sumSoFar AS soFar FROM orders o
//LEFT JOIN (SELECT p.order_id, SUM(p.price_amount) AS payment FROM Payments p GROUP BY order_id) AS payments ON o.id = payments.order_id
//LEFT JOIN (SELECT c.order_id AS cmp, SUM(EXTRACT(DAY FROM (CASE WHEN b.start_date > CURRENT_DATE::timestamp THEN CURRENT_DATE::timestamp ELSE b.start_date END ) - (CASE WHEN b.end_date < CURRENT_DATE::timestamp THEN b.end_date ELSE CURRENT_DATE::timestamp END)) * pl.price_amount ) AS sumSoFar FROM product_placement pl INNER JOIN bookings b ON b.placement_id = pl.id INNER JOIN campaigns c ON c.booking_id = b.id GROUP BY c.id) AS psf ON psf.cmp = o.id
//WHERE o.id in ('cf6e9785-c80d-43d7-baf2-c177057aeae0') GROUP BY o.id,payments.payment, psf.sumSoFar;
