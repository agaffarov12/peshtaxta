<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\Callback;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Regex;

class LocationInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add([
            'name' => "latitude",
            'validators' => [
                [
                    'name' => Regex::class,
                    'options' => [
                        'pattern' => "/^(-?\d+(\.\d+)?)/",
                    ]
                ],
            ],
            'filters' => [
                [
                    'name' => Callback::class,
                     'options' => [
                         'callback' => 'strval'
                     ],
                ],
            ],
        ]);

        $this->add([
            'name' => "longitude",
            'validators' => [
                [
                    'name' => Regex::class,
                    'options' => [
                        'pattern' => "/^(-?\d+(\.\d+)?)/",
                    ],
                ],
            ],
            'filters' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => 'strval'
                    ],
                ],
            ],
        ]);
    }
}
