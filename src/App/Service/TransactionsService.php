<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\TransactionDto;
use App\Dto\TransferTransactionDto;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Exception\NotEnoughMoneyException;
use Campaign\PaymentType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

class TransactionsService
{
    public function __construct(
        private readonly EntityManagerInterface $orm,
        private readonly AccountsService $accountsService,
        private readonly TransactionCategoriesService $transactionCategoriesService
    ) {}

    /**
     * @throws EntityNotFoundException
     * @throws NotEnoughMoneyException
     */
    public function create(TransactionDto $dto): UuidInterface
    {
        $account = $this->accountsService->get($dto->account);
        $type = TransactionType::from($dto->type);
        $amount = Money::UZS($dto->amount->amount);

        if ($type === TransactionType::EXPENDITURE) {
            $amount = $amount->negative();
        }

        $transaction = new Transaction(
            $type,
            $amount,
            $account,
            new DateTimeImmutable($dto->date),
            $dto->category ? $this->transactionCategoriesService->get($dto->category) : null,
            $dto->comment
        );

        $account->processTransaction($transaction);

        $this->orm->persist($transaction);
        $this->orm->flush();

        return $transaction->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function createWithPaymentType(PaymentType $paymentType, Money $amount): void
    {
        $account = $this->accountsService->getAccountWithPaymentType($paymentType);

        $transaction = new Transaction(
            TransactionType::INCOME,
            $amount,
            $account,
            new DateTimeImmutable(),
            null,
            null,
        );

        $account->processTransaction($transaction);

        $this->orm->persist($transaction);
        $this->orm->flush();
    }

    /**
     * @throws EntityNotFoundException
     * @throws NotEnoughMoneyException
     */
    public function transferMoney(TransferTransactionDto $dto): void
    {
        $amount = Money::UZS($dto->amount->amount);

        $fromAccount = $this->accountsService->get($dto->fromAccount);
        $toAccount = $this->accountsService->get($dto->toAccount);

        $fromAccount->withdrawMoney($amount);
        $toAccount->addMoney($amount);

        $withdrawalTransaction = new Transaction(
            TransactionType::EXPENDITURE,
            $amount,
            $fromAccount,
            new DateTimeImmutable($dto->date),
            null,
            $dto->comment
        );

        $depositTransaction = new Transaction(
            TransactionType::INCOME,
            $amount,
            $toAccount,
            new DateTimeImmutable($dto->date),
            null,
            $dto->comment
        );

        $this->orm->persist($withdrawalTransaction);
        $this->orm->persist($depositTransaction);

        $this->orm->flush();
    }

    public function list(int $offset = 0, int $limit = 15, string $category = null, string $account = null): Paginator
    {
        $builder = $this->orm->createQueryBuilder();

        $builder->select("t")->from(Transaction::class, "t");

        if ($category && trim($category)) {
            $builder
                ->join("t.category", "cat", "WITH", "cat.id = :categoryId")
                ->setParameter("categoryId", $category);
        }

        if ($account && trim($account)) {
            $builder
                ->join("t.account", "account", "WITH", "account.id = :accountId")
                ->setParameter("accountId", $account);
        }

        $query = $builder->getQuery();

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return new Paginator($query);
    }

    public function get()
    {

    }

    public function find()
    {

    }

}
