<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\DirectAdvertiserDto;
use App\InputFilter\DirectAdvertiserInputFilter;
use App\Service\ClientsService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateDirectAdvertiserHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly DirectAdvertiserInputFilter $inputFilter,
        private readonly ClientsService $service
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = array_merge_recursive($request->getParsedBody(), $request->getUploadedFiles());

        $this->inputFilter->setData($input);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $this->service->createDirectAdvertiser(DirectAdvertiserDto::fromArray($this->inputFilter->getValues()));

        return new JsonResponse(["ok"]);
    }
}
