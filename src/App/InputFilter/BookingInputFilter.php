<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\Uuid;
use Product\BookingPriority;
use Product\ProductType;

class BookingInputFilter extends InputFilter
{
    const DATETIME_FORMAT = DATE_RFC3339_EXTENDED;

    public function init(): void
    {
        $this->add([
            'name' => 'client',
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'placement',
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'startDate',
            'required' => true,
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => self::DATETIME_FORMAT
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'endDate',
            'required' => true,
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => self::DATETIME_FORMAT
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'priority',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => array_column(BookingPriority::cases(), 'value')
                    ],
                ],
            ],
        ]);
    }
}
