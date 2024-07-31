<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\AccountDto;
use App\InputFilter\AccountInputFilter;
use App\Service\AccountsService;
use App\Service\StatisticsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateAccountHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly AccountsService $service,
        private readonly AccountInputFilter $inputFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setValidationGroup(["name", "types", "balance"]);
        $this->inputFilter->setData($request->getParsedBody());

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $this->service->create(AccountDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['entity not found'], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
