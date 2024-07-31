<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Filter\File\RenameUpload;
use Laminas\Filter\StringTrim;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\File\Extension;
use Laminas\Validator\Uuid;

class ProductPlacementInputFilter extends InputFilter
{
    public function __construct(private readonly string $filesUploadDirectory)
    {}

    public function init(): void
    {
        $this->add(
            [
                'name'       => "id",
                'required'   => false,
                'validators' => [
                    [
                        'name' => Uuid::class,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => "name",
                'required' => true,
                'filters'  => [
                    ['name' => StringTrim::class],
                ],
            ]
        );

        $this->add(
            [
                'type' => MoneyInputFilter::class
            ],
            'price'
        );

        $this->add(
            [
                'name'       => 'images',
                'type'       => FileInput::class,
                'required'   => false,
                'validators' => [
                    [
                        'name'    => Extension::class,
                        'options' => [
                            'extension' => ["png", "jpg"],
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
