<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\StringLength;

class MoneyInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name' => "amount",
                'required' => true,
                'validators' => [
                    [
                        'name' => Digits::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => "currency",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 3
                        ],
                    ],
                ],
            ]
        );


    }
}
