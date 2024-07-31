<?php
declare(strict_types=1);

namespace App\Service;

use DateInterval;
use DateTimeImmutable;

class StatisticsService
{
    public function __construct(
        private readonly ClientsService $clientsService,
        private readonly CampaignRepository $campaignRepository,
        private readonly PaymentsService $paymentsService,
        private readonly ProductRepository $productRepository,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    public function getClientStatistics(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): array
    {
        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $result = [];

        $result['numbers']['overall'] = $this->clientsService->countClients();
        $result['numbers']['custom'] = $this->clientsService->countClientsByDate($startDate, $endDate);
        $result['categories'] = $this->clientsService->countClientsByCategory($startDate, $endDate);
        $result['types'] = $this->clientsService->countClientsByType($startDate, $endDate);

        return $result;
    }

    public function getProductStatistics(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): array
    {
        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $result = [];

        $result['numbers']['overall'] = $this->productRepository->countProducts();
        $result['numbers']['custom'] = $this->productRepository->countProductsByDate($startDate, $endDate);
        $result['cities'] = $this->productRepository->countProductsByCity($startDate, $endDate);
        $result['categories'] = $this->productRepository->countProductsByCategory($startDate, $endDate);

        return $result;
    }

    public function getStatistics(?DateTimeImmutable $startDate = null, ?DateTimeImmutable $endDate = null): array
    {
        $stats['client'] = $this->getClientsStats($startDate, $endDate);
        $stats['campaigns'] = $this->getCampaignsStats($startDate, $endDate);
        $stats['profit'] = $this->getProfitsStats($startDate, $endDate);
        $stats['graph'] = $this->getGraphStats($startDate, $endDate);
        $stats['sumByOrigin'] = $this->paymentsService->getPaymentsSumByClientOrigin($startDate, $endDate);
        $stats['sumByType'] = $this->paymentsService->getPaymentsSumByPaymentType($startDate, $endDate);

        return $stats;
    }

    private function getClientsStats(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null)
    {
        $stats = [];

        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $stats['todays'] = $this->clientsService->getNumberOfCreatedClientsBetween((new DateTimeImmutable())->setTime(0,0), new DateTimeImmutable());
        $stats['overall'] = $this->clientsService->getNumberOfCreatedClientsBetween();
        $stats['custom'] = $this->clientsService->getNumberOfCreatedClientsBetween($startDate, $endDate);

        return $stats;
    }

    private function getCampaignsStats(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        $stats = [];

        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $stats['todays'] = $this->campaignRepository->getNumberOfCreatedCampaignsBetween((new DateTimeImmutable())->setTime(0,0), new DateTimeImmutable());
        $stats['overall'] = $this->campaignRepository->getNumberOfCreatedCampaignsBetween();
        $stats['custom'] = $this->campaignRepository->getNumberOfCreatedCampaignsBetween($startDate, $endDate);

        return $stats;
    }

    private function getProfitsStats(DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        $stats = [];

        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        $result = $this->paymentsService->getProfitBetween((new DateTimeImmutable())->setTime(0,0), (new DateTimeImmutable())->setTime(23,59));
        $stats['todays']['overallProfit'] = $result[0][1] ?? 0;
        $avg = $this->orderRepository->getAverageOfPrices((new DateTimeImmutable())->setTime(0,0), (new DateTimeImmutable())->setTime(23,59))[0][1];
        $stats['todays']['average'] = number_format((float) $avg, 2, '.', '');
        $stats['todays']['netProfit'] = $result[0][2] ?? 0;

        $result = $this->paymentsService->getProfitBetween();
        $stats['overall']['overallProfit'] = $result[0][1] ?? 0;
        $avg = $this->orderRepository->getAverageOfPrices()[0][1];
        $stats['overall']['average'] = number_format((float) $avg, 2, '.', '');
        $stats['overall']['netProfit'] = $result[0][2] ?? 0;

        $result = $this->paymentsService->getProfitBetween($startDate, $endDate);
        $stats['custom']['overallProfit'] = $result[0][1] ?? 0;
        $avg = $this->orderRepository->getAverageOfPrices($startDate, $endDate)[0][1];
        $stats['custom']['average'] = number_format((float) $avg, 2, '.', '');
        $stats['custom']['netProfit'] = $result[0][2] ?? 0;

        return $stats;
    }

    private function getGraphStats(?DateTimeImmutable $startDate, ?DateTimeImmutable $endDate): array
    {
        if (!$startDate && !$endDate) {
            $endDate = new DateTimeImmutable();
            $startDate = $endDate->sub(new DateInterval("P1M"));
        }

        return $this->paymentsService->getDataForGraph($startDate, $endDate);
    }
}
