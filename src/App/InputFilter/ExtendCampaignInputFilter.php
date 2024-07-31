<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class ExtendCampaignInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add([
            'name' => 'campaignId',
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class,
                ],
            ],
        ]);

        $this->add(
           [
                'type' => BookingInputFilter::class,
           ],
            'booking'
        );
    }

}
