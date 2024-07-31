<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToFloat;
use Laminas\Filter\ToInt;
use Laminas\Filter\ToNull;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\Upload;
use Laminas\Validator\InArray;
use Laminas\Validator\IsCountable;
use Laminas\Validator\Uuid;
use Product\ProductType;

class ProductInputFilter extends InputFilter
{
    public function __construct(
        private readonly ProductPlacementInputFilter $placementInputFilter,
        private readonly string                      $filesUploadDirectory
    ) {
    }

    public function init(): void
    {
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
                'name'     => "region",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
            ]
        );

        $this->add(
            [
                'name'     => "city",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'viewingDistance',
                'required' => false,
                'filters' => [
                    [
                        'name' => ToNull::class,
                    ],
                    [
                        'name' => ToInt::class,
                    ],
                ],
            ],
        );

        $this->add(
            [
                'name' => 'trafficVolume',
                'required' => false,
                'filters' => [
                    [
                        'name' => ToNull::class,
                    ],
                    [
                        'name' => ToInt::class,
                    ],
                ],
            ],
        );

        $this->add(
            [
                'name' => 'transportPosition',
                'required' => false,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ],
                    [
                        'name' => StripTags::class,
                    ],
                ],
            ],
        );

        $this->add(
            [
                'name' => 'distanceToTrafficLight',
                'required' => false,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ],
                    [
                        'name' => StripTags::class,
                    ],
                ],
            ],
        );

        $this->add(
            [
                'name' => "width",
                'required' => false,
                'filters' => [
                    [
                        'name' => ToFloat::class
                    ],
                ],

            ]
        );

        $this->add(
            [
                'name' => "height",
                'required' => false,
                'filters' => [
                    [
                        'name' => ToFloat::class
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => "type",
                'required'   => true,
                'filters'    => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => InArray::class,
                        'options' => [
                            'haystack' => array_column(ProductType::cases(), 'value')
                        ]
                    ]
                ],
            ]
        );

        $this->add(
            [
                'name'       => "category",
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
                'type' => LocationInputFilter::class,
            ],
            'location'
        );

        $this->add(
            [
                'type'         => CollectionInputFilter::class,
                'required'     => true,
                'input_filter' => $this->placementInputFilter
            ],
            'placements'
        );

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
                'name' => "comment",
                "required" => false,
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
