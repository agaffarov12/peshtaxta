<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Filter\File\RenameUpload;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class CreativeInputFilter extends InputFilter
{
    public function __construct(private readonly string $filesUploadDirectory) {}

    public function init(): void
    {
        $this->add([
            'name' => "placementId",
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class,
                ],
            ],
        ]);

        $this->add([
            'name' => "file",
            'required' => true,
            'type' => FileInput::class,
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
        ]);
    }
}
