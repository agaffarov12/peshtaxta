<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class CategoryInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => "id",
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class
                ],
            ],
        ]);

        $this->add([
            'name' => "name",
            'required' => true,
            'filters'  => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => "parent",
            'required' => false,
            'validators' => [
                [
                    'name' => Uuid::class
                ],
            ],
            'filters' => [
                [
                    'name' => StringTrim::class,
                ],
            ],
        ]);
    }
}
