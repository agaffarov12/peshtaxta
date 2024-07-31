<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\CompanyDto;
use App\InputFilter\CompanyInputFilter;
use App\Service\ClientsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditCompanyClientHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ClientsService $service,
        private readonly CompanyInputFilter $inputFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

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

        try {
            $this->service->editCompanyClient($id, CompanyDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse([], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([]);
    }
    
}