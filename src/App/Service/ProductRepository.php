<?php
declare(strict_types=1);

namespace App\Service;

use Campaign\Campaign;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\PlacementStatus;
use Product\Product;
use Product\ProductId;
use \DateTimeImmutable;
use Campaign\Order;
use Product\ProductRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Query\Expr;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $orm)
    {
    }

    public function add(Product $product): void
    {
        $this->orm->persist($product);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function findById(ProductId | string $id): ?Product
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = ProductId::fromString($id);
        }

        $product = $this->orm->find(Product::class, $id);

        if ($product === null) {
            throw new EntityNotFoundException();
        }

        return $product;
    }

    public function findAll(): array
    {
        return $this->orm->getRepository(Product::class)->findAll();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(ProductId | string $id): void
    {
        $product = $this->findById($id);

        $this->orm->remove($product);
        $this->orm->flush();
    }

    public function getListOfProducts(
        int $offset = 0,
        int $limit = 10,
        string $name     = null,
        string $category = null,
        string $price    = null,
        string $tag      = null,
        string $status   = null,
        string $type     = null,
        string $region   = null,
        string $city     = null,
    ): Paginator {
        $builder = $this->orm->createQueryBuilder();
        $builder
            ->select("p")
            ->from(Product::class, "p")
            ->where($builder->expr()->eq("p.deleted", ":deleted"))
            ->setParameter("deleted", false);

        if ($name && trim($name)) {
            $builder
                ->andWhere($builder->expr()->eq("p.name", ":name"))
                ->setParameter("name", $name);
        }

        if($type && trim($type)) {
            $builder
                ->andWhere($builder->expr()->eq("p.type", ":type"))
                ->setParameter("type", $type);
        }

        if($region && trim($region)) {
            $builder
                ->andWhere($builder->expr()->eq("p.region", ":region"))
                ->setParameter("region", $region);
        }

        if($city && trim($city)) {
            $builder
                ->andWhere($builder->expr()->eq("p.city", ":city"))
                ->setParameter("city", $city);
        }

        if ($tag && trim($tag)) {
            $builder
                ->innerJoin("p.tags", "t", "WITH", "t.name = :tagName")
                ->setParameter("tagName", $tag);
        }

        if ($price) {
            $builder
                ->innerJoin("p.placements", "pl", "WITH", "pl.price.amount = :price")
                ->setParameter("price", (int) $price);
        }

        if ($category && trim($category)) {
            $builder
                ->join("p.category", "cat", "WITH", "cat.id = :id")
                ->setParameter("id", $category);
        }

        if($status && trim($status)) {
            if ($status === PlacementStatus::VACANT->value) {
                $builder->innerJoin("p.placements", "pl", "WITH", "pl.status = 'vacant'");
            } else {
                $builder
                    ->innerJoin("p.placements","pl");

                $builder->andWhere(
                    $builder->expr()->not(
                        $builder->expr()->exists(
                            "SELECT pla.id FROM Product\ProductPlacement pla WHERE pla.status = 'vacant' AND pla.productId = p.id"
                        )
                    )
                );

                //$builder->setParameter('productId', 'p.id');
            }
        }

        $query = $builder->setFirstResult($offset)->setMaxResults($limit)->getQuery();

        return new Paginator($query);
    }

    public function countProducts(): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("COUNT(p.id) as all")
            ->from(Product::class, "p")
            ->addSelect("(SELECT COUNT(p1.id) FROM Product\Product p1 JOIN p1.placements pl WHERE pl.status = 'vacant') as vacant")
            ->addSelect("(SELECT COUNT(p2.id) FROM Product\Product p2 JOIN p2.placements pl1 WHERE NOT EXISTS ( SELECT pl2.id FROM Product\ProductPlacement pl2 WHERE pl2.productId = p2.id AND pl2.status = 'vacant' )) as occupied");

        return $builder->getQuery()->getResult()[0];
    }

    public function countProductsByDate(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("COUNT(p.id) as all")
            ->from(Product::class, "p")
            ->where($builder->expr()->between("p.createdAt", ":start", ":end"))
            ->addSelect("(SELECT COUNT(p1.id) FROM Product\Product p1 JOIN p1.placements pl WHERE pl.status = 'vacant' AND p1.createdAt BETWEEN :start AND :end) as vacant")
            ->addSelect("(SELECT COUNT(p2.id) FROM Product\Product p2 JOIN p2.placements pl1 WHERE NOT EXISTS ( SELECT pl2.id FROM Product\ProductPlacement pl2 WHERE pl2.productId = p2.id AND pl2.status = 'vacant' ) AND p2.createdAt BETWEEN :start AND :end) as occupied")
            ->setParameter("start", $startDate)->setParameter("end", $endDate);

        return $builder->getQuery()->getResult()[0];
    }

    public function countProductsByCity(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["(p.city) as city, COUNT(DISTINCT p.id) as count, SUM(payment.price.amount) as price"])
            ->from(Product::class, "p")
            ->where($builder->expr()->between("p.createdAt", ":start", ":end"))
            ->leftJoin(Campaign::class, "c", "WITH", "c.productId = p.id")
            ->leftJoin(Order::class, "o", "WITH", "o.id = c.orderId")
            ->leftJoin("o.payments", "payment")
            ->setParameter("start", $startDate)->setParameter("end", $endDate)
            ->groupBy("p.city");

        return $builder->getQuery()->getResult();    
    }

    public function countProductsByCategory(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["(cat.name) as name, COUNT(DISTINCT p.id) as count, SUM(payment.price.amount) as price"])
            ->from(Product::class, "p")
            ->where($builder->expr()->between("p.createdAt", ":start", ":end"))
            ->join("p.category", "cat")
            ->leftJoin(Campaign::class, "c", "WITH", "c.productId = p.id")
            ->leftJoin(Order::class, "o", "WITH", "o.id = c.orderId")
            ->leftJoin("o.payments", "payment")
            ->setParameter("start", $startDate)->setParameter("end", $endDate)
            ->groupBy("cat.id");

        return $builder->getQuery()->getResult();
    }
}
