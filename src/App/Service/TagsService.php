<?php
declare(strict_types=1);

namespace App\Service;

use Common\Entity\Tag;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Ramsey\Uuid\UuidInterface;

class TagsService
{
    public function __construct(private readonly EntityManager $orm)
    {
    }

    public function addTags(array $tagNames): array
    {
        $existingTags = $this->getExistingTags($tagNames);
        $newTags = array_diff($tagNames, array_map(fn(Tag $t) => $t->getName(), $existingTags));

        return array_merge($existingTags, array_map(fn(string $t) => new Tag($t), $newTags));
    }

    private function getExistingTags(array $tags): array
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("t")
            ->from(Tag::class, "t")
            ->where($builder->expr()->in("t.name", ":names"))
            ->setParameter("names", $tags);

        return $builder->getQuery()->getResult();
    }

    public function findAll(): array
    {
        $query = $this->orm->createQuery("SELECT t FROM Common\Entity\Tag t");

        return $query->getResult();
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag($name);

        $this->orm->persist($tag);
        $this->orm->flush();

        return $tag;
    }

    private function findTagByName(string $name): ?Tag
    {
        return $this->orm->getRepository(Tag::class)->findOneBy(['name' => $name]);
    }

    private function find(UuidInterface|string $tagId): ?Tag
    {
        return $this->orm->find(Tag::class, $tagId);
    }
}
