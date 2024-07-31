<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\AccountDto;
use App\InputFilter\AccountInputFilter;
use App\Service\AccountsService;
use App\Utils\Arrays;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditAccountHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly AccountsService $service,
        private readonly AccountInputFilter $inputFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = Arrays::columns(
            array_merge_recursive($request->getAttributes(), $request->getParsedBody()),
            ['id', 'name', 'types', 'balance']
        );
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $this->service->edit(AccountDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => "Not found"], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
