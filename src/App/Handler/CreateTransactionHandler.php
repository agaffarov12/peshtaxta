<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\TransactionDto;
use App\Exception\NotEnoughMoneyException;
use App\InputFilter\TransactionInputFilter;
use App\Service\TransactionsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\I18n\Translator\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const Grpc\STATUS_NOT_FOUND;

class CreateTransactionHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TransactionsService $service,
        private readonly TransactionInputFilter $inputFilter,
        private readonly Translator $translator,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
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
            $this->service->create(TransactionDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], StatusCodeInterface::STATUS_NOT_FOUND);
        } catch (NotEnoughMoneyException $e) {
            return new JsonResponse(['error' => $this->translator->translate("accountNotEnoughMoney")], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
