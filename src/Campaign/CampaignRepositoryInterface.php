<?php
declare(strict_types=1);

namespace src\Campaign;

interface CampaignRepositoryInterface
{
    public function add();
    public function findById(CampaignId $id);
}
