<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Insight;
use App\Entity\InsightEventType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Ramsey\Uuid\Uuid;

class InsightsService
{
    public function __construct(private readonly EntityManager $orm)
    {}

    public function add(Insight $insight): void
    {
        if ($this->findByEvent($insight->getRelatedEntity(), $insight->getEventType()) !== null) {
            return;
        }

        $this->orm->persist($insight);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function markAsRead(string $id): void
    {
        $insight = $this->get($id);

        $insight->markAsRead();

        $this->orm->flush();
    }

    public function markAsReadByEvent(string $relatedEntity, InsightEventType $eventType): void
    {
        $insight = $this->findByEvent($relatedEntity, $eventType);

        if (!$insight) {
            return;
        }

        $insight->markAsRead();

        $this->orm->flush();
    }

    public function list(?string $context = null, bool $read = false): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("i")
            ->from(Insight::class, "i")
            ->where($builder->expr()->eq("i.read", ":read"))
            ->setParameter("read", $read);

        if ($context && trim($context)) {
            $builder->andWhere($builder->expr()->eq("i.context", ":context"))->setParameter("context", $context);
        }

        return $builder->getQuery()->getResult();
    }

    private function findByEvent(string $entityId, InsightEventType $eventType): ?Insight
    {
        $repository = $this->orm->getRepository(Insight::class);

        return $repository->findOneBy(['relatedEntity' => $entityId, 'eventType' => $eventType->value]);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(Uuid | string $id): ?Insight
    {
        if (is_string($id) && !Uuid::isValid($id)) {
            throw new EntityNotFoundException();
        }

        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        $insight = $this->orm->find(Insight::class, $id);

        if ($insight === null) {
            throw new EntityNotFoundException();
        }

        return $insight;
    }
}
