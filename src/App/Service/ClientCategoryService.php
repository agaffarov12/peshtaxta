<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ClientCategory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\EntityNotFoundException;

class ClientCategoryService 
{
    public function __construct(private readonly EntityManager $orm)
    {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    public function create(string $name, ?string $parent): UuidInterface
    {
        $category = new ClientCategory($name);

        if ($parent) {
            $parentCategory = $this->get($parent);

            $parentCategory->addChild($category);
            $category->setParent($parentCategory);

            $this->orm->persist($parentCategory);
        }

        $this->orm->persist($category);
        $this->orm->flush();

        return $category->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $category = $this->get($id);

        $category->disable();

        $this->orm->persist($category);
        $this->orm->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     * @throws ORMException
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

    public function get(UuidInterface | string $id): ?ClientCategory
    {
        if (is_string($id) && Uuid::isValid($id)) {
            $id = Uuid::fromString($id);
        }

        $category = $this->find($id);

        if (!$category) {
            throw new EntityNotFoundException();
        }

        return $category;
    }

    private function find(UuidInterface $id): ?ClientCategory
    {
        return $this->orm->find(ClientCategory::class, $id);
    }

    public function findAll(): array
    {
        return $this->orm->getRepository(ClientCategory::class)->findBy(['disabled' => false]);
    }
}
