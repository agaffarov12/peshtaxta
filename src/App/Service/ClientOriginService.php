<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ClientOrigin;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\EntityNotFoundException;

class ClientOriginService 
{
    public function __construct(private readonly EntityManager $orm)
    {}
    
    public function create(string $name): UuidInterface
    {
        $clientOrigin = new ClientOrigin($name);

        $this->orm->persist($clientOrigin);
        $this->orm->flush();

        return $clientOrigin->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function edit(string $id, string $name): void
    {
        $clientOrigin = $this->get($id);

        $clientOrigin->setName($name);

        $this->orm->flush();
    }

    public function list(int $offset, int $limit): Paginator
    {
        $query = $this->orm->createQuery('SELECT c FROM App\Entity\ClientOrigin c WHERE c.disabled = :disabled');
        $query->setParameter("disabled", false);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return new Paginator($query);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $origin = $this->get($id);

        $origin->disable();

        $this->orm->flush();
    }

    /**
    * @throws EntityNotFoundException
    */
    public function get(UuidInterface | string $id): ClientOrigin
    {
        if (is_string($id) && Uuid::isValid($id)) {
            $id = Uuid::fromString($id);
        }

        $origin = $this->find($id);

        if (!$origin) {
            throw new EntityNotFoundException();
        }

        return $origin;
    }

    public function find(UuidInterface $id): ?ClientOrigin
    {
        return $this->orm->find(ClientOrigin::class, $id);
    }
}
