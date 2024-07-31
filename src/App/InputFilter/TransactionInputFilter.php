<?php
declare(strict_types=1);

namespace App\InputFilter;

use App\Entity\TransactionType;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\Uuid;

class TransactionInputFilter extends InputFilter
{
    const DATETIME_FORMAT = DATE_RFC3339_EXTENDED;

    public function init(): void
    {
        $this->add(
            [
                'name'       => "type",
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => array_column(TransactionType::cases(), 'value')
                        ]
                    ]
                ],
            ]
        );

        $this->add(
            [
                'type' => MoneyInputFilter::class,
            ],
            'amount'
        );

        $this->add(
            [
                'name' => "account",
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
                'name' => "category",
                'required'   => false,
                'validators' => [
                    [
                        'name' => Uuid::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => "comment",
                'required' => false,
                'filters' => [
                    [
                        'name' => StringTrim::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => "date",
                'required' => true,
                'validators' => [
                    [
                        'name' => Date::class,
                        'options' => [
                            'format' => self::DATETIME_FORMAT
                        ],
                    ],
                ],
            ]
        );
    }
}
