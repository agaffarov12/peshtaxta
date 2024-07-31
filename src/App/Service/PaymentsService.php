<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Transaction;
use Campaign\Order;
use Campaign\Payment;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;

class PaymentsService
{
    public function __construct(private readonly EntityManager $orm)
    {}

    public function getProfitBetween(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null)
    {
        $builder = $this->orm->createQueryBuilder(Transaction::class);

        $builder
            ->select('SUM(CASE WHEN p.amount.amount > 0 THEN p.amount.amount ELSE 0 END), SUM(p.amount.amount)')
            ->from(Transaction::class, "p");

        if ($startDate !== null && $endDate !== null) {
            $builder
                ->where($builder->expr()->between("p.date", ":start", ":end"))
                ->setParameter("start", $startDate)
                ->setParameter("end", $endDate);
        }

        return $builder->getQuery()->getResult();
    }

    public function getDataForGraph(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $builder = $this->orm->createQueryBuilder(Payment::class);

        $result = $builder
            ->select(['p.price.amount as price', 'p.date'])
            ->from(Payment::class, "p")
            ->where($builder->expr()->between("p.date", ":start", ":end"))
            ->setParameter("start", $startDate)
            ->setParameter("end", $endDate)
            ->orderBy("p.date", "ASC")
            ->getQuery()->getResult();

        return $result;
    }

    public function getPaymentsSumByClientOrigin(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $builder = $this->orm->createQueryBuilder(Payment::class);

        $builder
            ->select(['origin.name, SUM(p.price.amount) as sum'])
            ->addSelect("(SELECT COUNT(cl.id) FROM App\Entity\Client cl WHERE cl.createdAt BETWEEN :start AND :end) AS count")
            ->from(Payment::class, 'p')
            ->where($builder->expr()->between("p.date", ":start", ":end"))
            ->join("p.order", "o")
            ->join(Client::class, "c", "WITH", 'c.id = o.clientId')
            ->join("c.origin", "origin")
            ->groupBy("c.origin")
            ->groupBy("origin.name")
            ->setParameter("start", $startDate)
            ->setParameter("end", $endDate);

       return $builder->getQuery()->getResult();
    }

    public function getPaymentsSumByPaymentType(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null)
    {
        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $builder = $this->orm->createQueryBuilder(Payment::class);

        $builder
            ->select(["t.name, SUM(p.price.amount) as sum"])
            ->addSelect("(SELECT COUNT(cl.id) FROM App\Entity\Client cl WHERE cl.createdAt BETWEEN :start AND :end) AS count")
            ->from(Payment::class, "p")
            ->where($builder->expr()->between("p.date", ":start", ":end"))
            ->join("p.type", "t")
            ->groupBy("t.id")
            ->setParameter("start", $startDate)
            ->setParameter("end", $endDate);

        return $builder->getQuery()->getResult();    
    }
}
