<?php
declare(strict_types=1);

namespace App\InputFilter;

use App\Entity\ClientType;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\File\Extension;
use Laminas\Validator\InArray;
use Laminas\Validator\IsCountable;
use Laminas\Validator\Uuid;

class CompanyInputFilter extends InputFilter
{
    public function __construct(private readonly string $filesUploadDirectory)
    {
    }

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
                'required' => false,
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
                'name' => "origin",
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

        $this->add([
            'name' => "name",
            'required' => true,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => 'address',
            'required' => true,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => 'mainBank',
            'required' => true,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => 'mfo',
            'required' => true,
            'validators' => [
                [
                    'name' => Digits::class,
                ],
            ],
            'filters'    => [
                ['name' => ToInt::class],
            ],
        ]);

        $this->add([
            'name' => 'mainXr',
            'required' => true,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => 'inn',
            'required' => true,
            'filters'    => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => Digits::class,
                ],
            ],
        ]);

        $this->add([
            'name' => "okonx",
            'required' => false,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => "additionalBank",
            'required' => false,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => "additionalMfo",
            'required' => false,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Digits::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'additionalXr',
            'required' => false,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
        ]);

        $this->add(
            [
                'name'       => "tags",
                'required'   => false,
                'validators' => [
                    [
                        'name' => isCountable::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'files',
                'type'       => FileInput::class,
                'required'   => false,
                'validators' => [
                    [
                        'name'    => Extension::class,
                        'options' => [
                            'extension' => ["png", "jpg", "pdf", "doc", "docx"],
                        ],
                    ],
                ],
                'filters'    => [
                    [
                        'name'    => RenameUpload::class,
                        'options' => [
                            'target'               => $this->filesUploadDirectory,
                            'randomize'            => true,
                            'use_upload_name'      => true,
                            'overwrite'            => true,
                            'use_upload_extension' => true,
                            'stream_factory'       => new StreamFactory(),
                            'upload_file_factory'  => new UploadedFileFactory(),
                        ],
                    ],
                ],
            ]
        );
    }
}
