<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\FilesService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use finfo;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FileDownloadHandler implements RequestHandlerInterface
{
    public function __construct(private readonly FilesService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        try {
            $file = $this->service->get($id);
            $path = $file->getPath();
            $arr = explode("/", $path);
            $fileName = array_pop($arr);

        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['not found'], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromFile($path);

        return new Response(
            $stream,
            headers: [
                'Content-Type' => (new finfo(FILEINFO_MIME))->file($path),
                'Content-Disposition' => "attachment; filename={$fileName}",
                'Content-Transfer-Encoding' => "Binary",
                'Content-Description' => "File Transfer",
                'Pragma' => "Public",
                'Expires' => 0,
                'Cache-Control' => "must-revalidate",
                'Content-Length' => $stream->getSize(),
                'Access-Control-Expose-Headers' => 'fileName',
                'fileName' => $fileName,
            ]
        );
    }
}
