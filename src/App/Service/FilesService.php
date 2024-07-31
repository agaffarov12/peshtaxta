<?php
declare(strict_types=1);

namespace App\Service;

use Common\Entity\File;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Ramsey\Uuid\Uuid;

class FilesService
{
    public function __construct(private readonly EntityManager $orm)
    {}

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $id): ?File
    {
        $id = Uuid::fromString($id);

        $file = $this->orm->find(File::class, $id);

        if (!$file) {
            throw new EntityNotFoundException();
        }

        return $file;
    }

}
