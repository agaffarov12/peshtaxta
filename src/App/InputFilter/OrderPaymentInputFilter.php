<?php
declare(strict_types=1);

namespace App\InputFilter;

use Campaign\PaymentType;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\Uuid;

class OrderPaymentInputFilter extends InputFilter
{
    const DATETIME_FORMAT = DATE_RFC3339_EXTENDED;

    public function init(): void
    {
        $this->add([
            'name' => 'order',
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class,
                ],
            ],
        ]);

        $this->add(
            [
                'type' => MoneyInputFilter::class
            ],
            'price'
        );

        $this->add(
            [
                'name' => 'type',
                'required' => true,
                'validators' => [
                    [
                        'name' => Uuid::class,
                    ],
                ],
            ],
        );

        //$this->add(
        //    [
        //        'type' => TransactionInputFilter::class
        //    ],
        //    'transaction'
        //);
    }
}
