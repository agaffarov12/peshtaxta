<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\IsCountable;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;

class ContactDetailsInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => "phoneNumbers",
            'required' => true,

        ]);

        $this->add([
            'name' => "email",
            'required' => false,
            'validators' => [
                [
                    'name'    => EmailAddress::class,
                ],
            ],
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => "telegram",
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 5,
                    ],
                ]
            ],
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);
    }
}
