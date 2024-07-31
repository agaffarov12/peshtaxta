<?php
declare(strict_types=1);

namespace App\InputFilter;

use App\Entity\ClientType;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\InArray;
use Laminas\Validator\Uuid;

class ClientInputFilter extends InputFilter
{
    public function init(): void
    {
        $this->add(
            [
                'name' => "firstName",
                'required' => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
            ],
        );

        $this->add(
            [
                'name' => "lastName",
                'required' => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
            ],
        );

        $this->add(
            [
                'name' => "surname",
                'required' => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
            ],
        );

        $this->add(
            [
                'name' => "category",
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
                'type' => ContactDetailsInputFilter::class,
            ],
            'contactDetails'
        );

        $this->add(
            [
                'type' => CompanyDetailsInputFilter::class,
            ],
            'companyDetails'
        );

        $this->add(
            [
            'type' => PassportDetailsInputFilter::class,
            ],
            'passportDetails'
        );

        $this->add(
            [
                'name' => "type",
                'required' => true,
                'validators' => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => array_column(ClientType::cases(), 'value')
                        ]
                    ]
                ],
            ]
        );

        $this->add(
            [
                'name' => "comment",
                'required' => false,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
            ]
        );
    }
}
