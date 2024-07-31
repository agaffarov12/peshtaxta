<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\IsCountable;
use Laminas\Validator\Uuid;

class OrderInputFilter extends InputFilter
{
    public function __construct(private readonly ServiceInputFilter $serviceInputFilter)
    {}

    public function init()
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
                'name' => 'comment',
                'required' => false,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'campaigns',
                'required' => true,
                'validators' => [
                    [
                        'name' => isCountable::class
                    ],
                ],

            ]
        );

        $this->add(
            [
                'name'       => "tags",
                'required'   => false,
                'validators' => [
                    [
                        'name' => isCountable::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'type' => CollectionInputFilter::class,
                'required' => false,
                'input_filter' => $this->serviceInputFilter,
            ],
            'services'
        );
    }
}
