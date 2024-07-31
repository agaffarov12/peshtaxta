<?php
declare(strict_types=1);

namespace App\Service;

use Campaign\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PaymentTypesService
{
    public function __construct(private readonly EntityManagerInterface $orm)
    {}

    public function list(): array
    {
        return $this->orm->getRepository(PaymentType::class)->findBy(['disabled' => false]);
    }

    public function create(string $name): UuidInterface
    {
        $paymentType = new PaymentType($name);

        $this->orm->persist($paymentType);
        $this->orm->flush();

        return $paymentType->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $paymentType = $this->get($id);

        $paymentType->disable();

        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function edit(string $id, string $name): void
    {
        $paymentType = $this->get($id);

        $paymentType->setName($name);

        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(UuidInterface | string $id): PaymentType
    {
        if (is_string($id) && Uuid::isValid($id)) {
            $id = Uuid::fromString($id);
        }

        $paymentType = $this->find($id);

        if (!$paymentType) {
            throw new EntityNotFoundException();
        }

        return $paymentType;
    }

    public function findWithIds(array $ids): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("t")
            ->from(PaymentType::class, "t")
            ->where($builder->expr()->in("t.id", ":ids"))
            ->setParameter("ids", $ids);

        return $builder->getQuery()->getResult();
    }

    private function find(UuidInterface $id): ?PaymentType
    {
        return $this->orm->find(PaymentType::class, $id);
    }
}
