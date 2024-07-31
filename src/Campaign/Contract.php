<?php
declare(strict_types=1);

namespace Campaign;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Domain\Client;
use Domain\ContractId;
use Domain\Transaction;

class Contract
{
    private ContractId $id;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private Client $client;
    private Collection $campaigns;
    private string $comment;
    private Collection $tags;
    private Collection $files;
    private Transaction $transaction;

    public function __construct()
    {

    }
}
