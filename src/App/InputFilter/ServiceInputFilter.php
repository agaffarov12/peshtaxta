<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class ServiceInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name' => "id",
                'required' => false,
                'validators' => [
                    [
                        'name' => Uuid::class
                    ]
                ],
            ]
        );

        $this->add(
            [
                'name' => "name",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
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
                'filters' => [
                    [
                        'name' => StringTrim::class
                    ],
                ],
            ],
        );
    }
}
