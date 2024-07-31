<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class CampaignInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(
            [
                'name' => "id",
                'required' => false,
                'validators' => [
                    [
                        'name' => Uuid::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'type' => CreativeInputFilter::class
            ],
            'creative'
        );

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
                'name'       => "productId",
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
                'type' => BookingInputFilter::class,
            ],
            'booking'
        );
    }
}
