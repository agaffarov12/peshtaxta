<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\CompanyDto;
use App\Dto\DirectAdvertiserDto;
use App\Entity\Client;
use App\Entity\ClientType;
use App\Entity\Company;
use App\Entity\ContactDetails;
use App\Entity\DirectAdvertiser;
use App\Utils\FileUtil;
use Campaign\Campaign;
use Campaign\Order;
use Client\ClientId;
use Common\Entity\File;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Laminas\Diactoros\UploadedFile;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class ClientsService
{
    public function __construct(
        private readonly EntityManager $orm,
        private readonly ClientCategoryService $categoryService,
        private readonly TagsService $tagsService,
        private readonly ClientOriginService $clientOriginService,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    public function createDirectAdvertiser(DirectAdvertiserDto $dto): void
    {
        Assert::notNull($dto->clientProfile, "clientProfile must be specified");
        Assert::notNull($dto->clientProfile->contactDetails, "contactDetails must be specified");

        $files = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->clientProfile->files
        );

        $tags = empty($dto->clientProfile->tags) ? [] : $this->tagsService->addTags($dto->clientProfile->tags);

        $phoneNumbers = explode(",", $dto->clientProfile->contactDetails->phoneNumbers);

        $client = new DirectAdvertiser(
            $dto->clientProfile->firstName,
            $dto->clientProfile->lastName,
            new ContactDetails(
                $phoneNumbers[0],
                $phoneNumbers[1] ?? null,
                $phoneNumbers[2] ?? null,
                $dto->clientProfile->contactDetails->email,
                $dto->clientProfile->contactDetails->telegram
            ),
            ClientType::from($dto->clientProfile->type),
            $this->categoryService->get($dto->clientProfile->category),
            $this->clientOriginService->get($dto->clientProfile->origin),
            $tags,
            $files,
            $dto->seriesAndNumber,
            $dto->authority,
            $dto->inn === 0 ? null : $dto->inn,
            $dto->dateOfIssue === null ? null : new DateTimeImmutable($dto->dateOfIssue),
            $dto->dateOfBirth === null ? null : new DateTimeImmutable($dto->dateOfBirth),
            $dto->clientProfile->surname,
            $dto->clientProfile->comment
        );

        $this->orm->persist($client);
        $this->orm->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    public function createCompany(CompanyDto $dto): void
    {
        Assert::notNull($dto->clientProfile, "clientProfile must be specified");
        Assert::notNull($dto->clientProfile->contactDetails, "contactDetails must be specified");

        $files = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->clientProfile->files
        );

        $tags = empty($dto->clientProfile->tags) ? [] : $this->tagsService->addTags($dto->clientProfile->tags);

        $phoneNumbers = explode(",", $dto->clientProfile->contactDetails->phoneNumbers);

        $client = new Company(
            $dto->clientProfile->firstName,
            $dto->clientProfile->lastName,
            new ContactDetails(
                $phoneNumbers[0],
                $phoneNumbers[1] ?? null,
                $phoneNumbers[2] ?? null,
                $dto->clientProfile->contactDetails->email,
                $dto->clientProfile->contactDetails->telegram
            ),
            ClientType::from($dto->clientProfile->type),
            $this->categoryService->get($dto->clientProfile->category),
            $this->clientOriginService->get($dto->clientProfile->origin),
            $tags,
            $files,
            $dto->name,
            $dto->address,
            $dto->mainBank,
            $dto->mfo,
            $dto->mainXr,
            $dto->inn,
            $dto->okonx,
            $dto->additionalBank,
            $dto->additionalMfo,
            $dto->additionalXr,
            $dto->clientProfile->surname,
            $dto->clientProfile->comment,
        );

        $this->orm->persist($client);
        $this->orm->flush();
    }

    public function getListOfClients(
        int    $offset = 0,
        int    $limit = 10,
        string $firstName = null,
        string $lastName = null,
        string $category = null,
        string $phoneNumber = null,
        string $type = null,
        array  $tags = null,
        string $orderBy = "firstName"
    ): Paginator {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select('c')
            ->from(Client::class, 'c')
            ->where($builder->expr()->eq("c.deleted", ":deleted"))
            ->setParameter("deleted", false);

        if ($firstName && trim($firstName)) {
            $builder->andWhere($builder->expr()->eq("c.firstName", ":firstName"))
                ->setParameter("firstName", $firstName);
        }

        if ($lastName && trim($lastName)) {
            $builder->andWhere($builder->expr()->eq("c.lastName", ":lastName"))
                ->setParameter("lastName", $lastName);
        }

        if ($category && trim($category)) {
            $builder->join("c.category", "cat", "WITH", "cat.id = :id")
                ->setParameter("id", $category);
        }

        if ($phoneNumber && trim($phoneNumber)) {
            $builder
                ->andWhere($builder->expr()->eq("c.contactDetails.phoneNumber", ":number"))
                ->orWhere($builder->expr()->eq("c.contactDetails.phoneNumber2", ":number"))
                ->orWhere($builder->expr()->eq("c.contactDetails.phoneNumber3", ":number"))
                ->setParameter("number", $phoneNumber);
        }

        if (!empty($tags)) {
            $builder
                ->join("c.tags", "t", Expr\Join::WITH, $builder->expr()->in("t.id", ":ids"))
                ->setParameter("ids", $tags);
        }

        if ($type && trim($type)) {
            $builder->andWhere($builder->expr()->eq("c.type", ":type"))
                ->setParameter("type", $type);
        }

        $query = $builder->orderBy("c.firstName", "DESC")->setFirstResult($offset)->setMaxResults($limit)->getQuery();

        return new Paginator($query);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getIndividualClientDetails(string | UuidInterface $clientId): ?DirectAdvertiser
    {
        if (is_string($clientId) && !Uuid::isValid($clientId)) {
            throw new EntityNotFoundException();
        }

        if (is_string($clientId)) {
            $clientId = ClientId::fromString($clientId);
        }

        $client = $this->orm->find(DirectAdvertiser::class, $clientId);

        if ($client === null) {
            throw new EntityNotFoundException();
        }

        return $client;
    }

    public function getCompanyClientDetails(string | UuidInterface $clientId): ?Company
    {
        if (is_string($clientId) && !Uuid::isValid($clientId)) {
            throw new EntityNotFoundException();
        }

        if (is_string($clientId)) {
            $clientId = ClientId::fromString($clientId);
        }

        return $this->orm->find(Company::class, $clientId);    
    }

    /**
     * @throws EntityNotFoundException
     */
    public function setToIndebted(string $id): void
    {
        $client = $this->getClientById($id);

        $client->setIndebted(true);

        $this->orm->persist($client);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function checkForDebts(string $id): void
    {
        $client   = $this->getClientById($id);
        $query    = $this->orm->createQuery('SELECT o FROM Campaign\Order o WHERE o.clientId = :clientId');
        $indebted = false;

        $query->setParameter("clientId", $id);

        /** @var Order $order */
        foreach ($query->getResult() as $order) {
            if (!$order->isPaid()) {
                $indebted = true;
                break;
            }
        }

        $client->setIndebted($indebted);

        $this->orm->persist($client);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getClientById(string | UuidInterface $clientId): ?Client
    {
        if (is_string($clientId) && !Uuid::isValid($clientId)) {
            throw new EntityNotFoundException();
        }

        if (is_string($clientId)) {
            $clientId = ClientId::fromString($clientId);
        }

        $client = $this->orm->find(Client::class, $clientId);

        if (!$client) {
            throw new EntityNotFoundException();
        }

        return $client;
    }

    public function findAll()
    {
        return $this->orm->getRepository(Client::class)->findAll();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function editIndividualClient(string $id, DirectAdvertiserDto $dto): void
    {
        $client = $this->getIndividualClientDetails($id);

        $uploadedFiles = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->clientProfile->files
        );

        $this->handleFiles($uploadedFiles, $client);

        $tags = empty($dto->clientProfile->tags) ? [] : $this->tagsService->addTags($dto->clientProfile->tags);

        $phoneNumbers = explode(",", $dto->clientProfile->contactDetails->phoneNumbers);

        $client->setTags($tags);

        $client->setFirstName($dto->clientProfile->firstName);
        $client->setLastName($dto->clientProfile->lastName);
        $client->setSurname($dto->clientProfile->surname);
        $client->setCategory($this->categoryService->get($dto->clientProfile->category));
        $client->setOrigin($this->clientOriginService->get($dto->clientProfile->origin));
        $client->setComment($dto->clientProfile->comment);
        $client->setPassportSeriesAndNumber($dto->seriesAndNumber);

        if ($dto->dateOfIssue) {
            $client->setDateOfPassportIssue(new DateTimeImmutable($dto->dateOfIssue));
        }

        if($dto->dateOfBirth) {
            $client->setDateOfBirth(new DateTimeImmutable($dto->dateOfBirth));
        }

        $client->setPassportAuthority($dto->authority);

        if ($dto->inn) {
            $client->setPassportInn($dto->inn);
        }

        $client->setContactDetails(
            new ContactDetails(
                $phoneNumbers[0],
                $phoneNumbers[1] ?? null,
                $phoneNumbers[2] ?? null,
                $dto->clientProfile->contactDetails->email,
                $dto->clientProfile->contactDetails->telegram
            )
        );

        $this->orm->persist($client);
        $this->orm->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFoundException
     */
    public function editCompanyClient(string $id, CompanyDto $dto): void
    {
        $client = $this->getCompanyClientDetails($id);

        $uploadedFiles = array_map(
            fn(UploadedFile $file) => new File($file->getStream()->getMetadata()['uri']),
            $dto->clientProfile->files
        );

        $this->handleFiles($uploadedFiles, $client);

        $tags = empty($dto->clientProfile->tags) ? [] : $this->tagsService->addTags($dto->clientProfile->tags);

        $client->setTags($tags);

        $phoneNumbers = explode(",", $dto->clientProfile->contactDetails->phoneNumbers);

        $client->setFirstName($dto->clientProfile->firstName);
        $client->setLastName($dto->clientProfile->lastName);
        $client->setSurname($dto->clientProfile->surname);
        $client->setCategory($this->categoryService->get($dto->clientProfile->category));
        $client->setOrigin($this->clientOriginService->get($dto->clientProfile->origin));

        $client->setName($dto->name);
        $client->setAddress($dto->address);
        $client->setMainBank($dto->mainBank);
        $client->setMfo($dto->mfo);
        $client->setMainXr($dto->mainXr);
        $client->setInn($dto->inn);
        $client->setOkonx($dto->okonx);
        $client->setComment($dto->clientProfile->comment);

        if ($dto->additionalBank) {
            $client->setAdditionalBank($dto->additionalBank);
        }

        if ($dto->additionalMfo) {
            $client->setAdditionalMfo($dto->additionalMfo);
        }

        if ($dto->additionalXr) {
            $client->setAdditionalXr($dto->additionalXr);
        }

        $client->setContactDetails(
            new ContactDetails(
                $phoneNumbers[0],
                $phoneNumbers[1] ?? null,
                $phoneNumbers[2] ?? null,
                $dto->clientProfile->contactDetails->email,
                $dto->clientProfile->contactDetails->telegram
            )
        );

        $this->orm->persist($client);
        $this->orm->flush();
    }

    public function getNumberOfCreatedClientsBetween(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): mixed
    {
        $builder = $this->orm->createQueryBuilder(Client::class);
        
        $builder
            ->select($builder->expr()->count("c.id"))
            ->from(Client::class, "c");

        if ($startDate !== null && $endDate !== null) {
            $builder
                ->where($builder->expr()->between("c.createdAt", ":start", ":end"))
                ->setParameter("start", $startDate)
                ->setParameter("end", $endDate);
        }

        return $builder->getQuery()->getSingleScalarResult();
    }

    public function countClients()
    {
        $endDate = new DateTimeImmutable();
        $startDate = $endDate->sub(new DateInterval("P1M"));

        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select('COUNT(c.id) as latest')
            ->where($builder->expr()->between("c.createdAt", ":start", ":end"))
            ->from(Client::class, "c")
            ->addSelect('(SELECT COUNT(c1.id) FROM App\Entity\Client c1) as total')
            ->addSelect("(SELECT COUNT(c2.id) FROM App\Entity\Client c2 JOIN Campaign\Campaign ca WITH ca.status = 'active') as active")
            ->addSelect('(SELECT COUNT(c3.id) FROM App\Entity\Client c3 WHERE c3.indebted = :indebted) as indebted')
            ->setParameter("indebted", true)
            ->setParameter("start", $startDate)
            ->setParameter("end", $endDate);

        return $builder->getQuery()->getResult()[0];
    }

    public function countClientsByDate(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select('COUNT(c.id) as latest')
            ->where($builder->expr()->between("c.createdAt", ":start", ":end"))
            ->from(Client::class, "c")
            ->addSelect('(SELECT COUNT(c1.id) FROM App\Entity\Client c1 WHERE c1.createdAt < :end) as total')
            ->addSelect("(SELECT COUNT(c2.id) FROM App\Entity\Client c2 JOIN Campaign\Campaign ca WITH ca.status = 'active' WHERE c2.createdAt BETWEEN :start AND :end ) as active")
            ->addSelect('(SELECT COUNT(c3.id) FROM App\Entity\Client c3 WHERE (c3.createdAt BETWEEN :start AND :end) AND c3.indebted = :indebted) as indebted')
            ->setParameter("indebted", true)
            ->setParameter("start", $startDate)
            ->setParameter("end", $endDate);

        return $builder->getQuery()->getResult()[0];
    }

    public function countClientsByCategory(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["category.name as name, COUNT(DISTINCT client.id) as count, SUM(p.price.amount) as price" ])
            ->from(Client::class, "client")
            ->where($builder->expr()->between("client.createdAt", ":start", ":end"))
            ->join("client.category", "category")
            ->leftJoin(Order::class, 'o', 'WITH', 'o.clientId = client.id')
            ->leftJoin('o.payments', 'p')
            ->setParameter("start", $startDate)->setParameter("end", $endDate)
            ->groupBy("category.id");

        //$query = $this->orm->createQuery("SELECT SUM(p.price.amount) as price, c.firstName FROM App\Entity\Client c LEFT JOIN Campaign\Order o WITH o.clientId = c.id LEFT JOIN o.payments p GROUP BY c.id");
        return $builder->getQuery()->getResult();
    }

    public function countClientsByType(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select(["client.type as name, COUNT(DISTINCT client.id) as count, SUM(p.price.amount) as price"])
            ->from(Client::class, "client")
            ->where($builder->expr()->between("client.createdAt", ":start", ":end"))
            ->leftJoin(Order::class, 'o', 'WITH', 'o.clientId = client.id')
            ->leftJoin('o.payments', 'p')
            ->setParameter("start", $startDate)->setParameter("end", $endDate)
            ->groupBy('client.type');

        return $builder->getQuery()->getResult();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function deleteClient(string $id): void
    {
        $client = $this->getClientById($id);

        $client->delete();

        $this->orm->persist($client);
        $this->orm->flush();
    }

    private function handleFiles(array $uploadedFiles, Client &$client): void
    {
        $newFiles = $this->getNewFiles($client->getFiles()->toArray(), $uploadedFiles, $client);

        foreach($newFiles as $file) {
            $client->addFile($file);
        }
    }

    private function getNewFiles(array $clientFiles, array $uploadedFiles, Client &$client): array
    {
        $newFiles = [];
        $filesToDelete = [];

        foreach($uploadedFiles as $key => $uploadedFile) {
            foreach ($clientFiles as $clientFile) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $clientFile->getPath())) {
                    $filesToDelete[] = $uploadedFile->getPath();
                    continue 2;
                }
            }
            $newFiles[] = $uploadedFile;
        }

        /** @var File $value */
        foreach($client->getFiles() as $key => $value) {
            foreach ($uploadedFiles as $uploadedFile) {
                if (FileUtil::filesIdentical($uploadedFile->getPath(), $value->getPath())) {
                    continue 2;
                }
            }
            $client->removeFile($key);
        }

        foreach ($filesToDelete as $f) {
            FileUtil::deleteFile($f);
        }
        return $newFiles;
    }
}
