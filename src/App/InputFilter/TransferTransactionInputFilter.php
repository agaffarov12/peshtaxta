<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;
use Laminas\Validator\Uuid;

class TransferTransactionInputFilter extends InputFilter
{
    const DATETIME_FORMAT = DATE_RFC3339_EXTENDED;

    public function init(): void
    {
        $this->add(
            [
                'type' => MoneyInputFilter::class,
            ],
            'amount'
        );

        $this->add(
            [
                'name' => "fromAccount",
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
                'name' => "toAccount",
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
    }
}
