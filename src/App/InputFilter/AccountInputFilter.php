<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Validator\Digits;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\IsCountable;
use Laminas\Validator\Uuid;

class AccountInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    [
                        'name' => Uuid::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'     => "name",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
            ]
        );

        $this->add(
            [
                'name'       => "types",
                'required'   => true,
                'validators' => [
                    [
                        'name' => IsCountable::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => "balance",
                'required' => false,
                'validators' => [
                    [
                        'name' => Digits::class,
                    ]
                ],
            ]
        );
    }
}
