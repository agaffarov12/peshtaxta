<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\TransactionCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TransactionCategoriesService
{
    public function __construct(private readonly EntityManagerInterface $orm)
    {}

    /**
     * @throws EntityNotFoundException
     */
    public function create(string $name, ?string $parent): UuidInterface
    {
        $transactionCategory = new TransactionCategory($name);

        if ($parent) {
            $parentCategory = $this->get($parent);

            $parentCategory->addChild($transactionCategory);
            $transactionCategory->setParent($parentCategory);

            $this->orm->persist($parentCategory);
        }

        $this->orm->persist($transactionCategory);
        $this->orm->flush();

        return $transactionCategory->getId();
    }

    public function list(): array
    {
        return $this->orm->getRepository(TransactionCategory::class)->findBy(['disabled' => false]);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(UuidInterface | string $id): void
    {
        $transactionCategory = $this->get($id);

        $transactionCategory->disable();

        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function edit(string $id, string $name, ?string $parent): void
    {
        $category = $this->get($id);

        $category->setName($name);

        $parentCategory = ($parent !== null && trim($parent)) ? $this->get($parent) : null;
       
        $category->setParent($parentCategory);

        $this->orm->persist($category);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(UuidInterface | string $id): TransactionCategory
    {
        if (is_string($id) && Uuid::isValid($id)) {
            $id = Uuid::fromString($id);
        }

        $transactionCategory = $this->find($id);

        if (!$transactionCategory) {
            throw new EntityNotFoundException("Transaction category not found");
        }

        return $transactionCategory;
    }

    private function find(UuidInterface $id): ?TransactionCategory
    {
        return $this->orm->find(TransactionCategory::class, $id);
    }
}
