<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\AccountDto;
use App\Entity\Account;
use App\Exception\NotEnoughMoneyException;
use Campaign\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AccountsService
{
    public function __construct(
        private readonly EntityManagerInterface $orm,
        private readonly PaymentTypesService $paymentTypesService
    ) {}

    /**
     * @throws EntityNotFoundException
     */
    public function create(AccountDto $dto): UuidInterface
    {
        $paymentTypes = $this->paymentTypesService->findWithIds($dto->types);

        $account = new Account(
            $dto->name,
            $paymentTypes,
            $dto->balance ? Money::UZS($dto->balance) : null,
        );

        foreach ($paymentTypes as $paymentType) {
            $paymentType->setAccount($account);
        }

        $this->orm->persist($account);
        $this->orm->flush();

        return $account->getId();
    }

    public function list(): array
    {
        return $this->orm->getRepository(Account::class)->findBy(['disabled' => false]);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(UuidInterface | string $id): void
    {
        $account = $this->get($id);

        $account->disable();

        $account->setPaymentTypes([]);

        foreach ($account->getPaymentTypes() as $p) {
            $p->setAccount(null);
        }

        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(UuidInterface | string $id): Account
    {
        if (is_string($id) && Uuid::isValid($id)) {
            $id = Uuid::fromString($id);
        }

        $account = $this->find($id);

        if (!$account) {
            throw new EntityNotFoundException("Account not found");
        }

        return $account;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAccountWithPaymentType(PaymentType $type)
    {
        $builder = $this->orm->createQueryBuilder();

        $builder
            ->select("a")
            ->from(Account::class, "a")
            ->innerJoin("a.paymentTypes", "p", 'WITH', 'p.id = :id')
            ->setParameter("id", $type->getId());

        try {
            $account = $builder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }

        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    private function find(UuidInterface $id): ?Account
    {
        return $this->orm->find(Account::class, $id);
    }

    private function comparePaymentTypes(PaymentType $a, PaymentType $b): int
    {
        return (string) $a->getId() === (string) $b->getId() ? 0 : -1;
    }
    /**
     * @throws EntityNotFoundException
     */
    public function edit(AccountDto $dto): void
    {
        $account = $this->get($dto->id);

        $paymentTypes = $this->paymentTypesService->findWithIds($dto->types);

        $newPaymentTypes = array_udiff(
            $paymentTypes,
            $account->getPaymentTypes()->toArray(),
            $this->comparePaymentTypes(...)
        );

        $intersection = array_uintersect(
            $paymentTypes,
            $account->getPaymentTypes()->toArray(),
            $this->comparePaymentTypes(...)
        );

        $paymentsToDelete = array_udiff(
            $account->getPaymentTypes()->toArray(),
            $intersection,
            $this->comparePaymentTypes(...)
        );

        $account->setPaymentTypes($paymentTypes);
        $account->setName($dto->name);

        foreach ($newPaymentTypes as $p) {
            $p->setAccount($account);
        }

        foreach ($paymentsToDelete as $p) {
            $p->setAccount(null);
        }

        $this->orm->flush();
    }
}
