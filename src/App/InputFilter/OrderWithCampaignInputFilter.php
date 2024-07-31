<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class OrderWithCampaignInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name'       => "clientId",
                'required'   => true,
                'validators' => [
                    [
                        'name' => Uuid::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'type' => MoneyInputFilter::class
            ],
            'price'
        );

        $this->add(
            [
                'type' => CampaignInputFilter::class
            ],
            'campaign'
        );
    }
}
