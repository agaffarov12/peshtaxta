<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;

class RegionsService
{
    public function __construct(private readonly EntityManagerInterface $orm)
    {}

    public function list(): array
    {
        return $this->orm->getRepository(Region::class)->findBy(['parent' => null]);
    }

    public function toggleList(array $regionsArray): void
    {
        $ids = array_keys($regionsArray);

        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("r")
            ->from(Region::class, "r")
            ->where($builder->expr()->in("r.id", ":ids"))
            ->setParameter("ids", $ids);

        $regions = $builder->getQuery()->getResult();

        foreach($regions as $region) {
            $region->setEnabled($regionsArray[(string) $region->getId()]);
        }

        $this->orm->flush();
    }

    public function createRegionsFromFile(string $filePath): void
    {
        $regionsArray = json_decode(file_get_contents($filePath));

        if (!$regionsArray) {
            echo("null");
            return;
        }

        foreach ($regionsArray as $key => $value) {
            $regionInstance = new Region($key);

            foreach ($value as $v) {
                $childRegion = new Region($v);

                $childRegion->setParent($regionInstance);
                $regionInstance->addChildRegion($childRegion);
            }
            $this->orm->persist($regionInstance);
        }

        $this->orm->flush();
    }
}
