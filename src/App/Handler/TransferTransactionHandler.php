<?php
declare(strict_types=1);

namespace App\Handler;

use Doctrine\ORM\EntityNotFoundException;
use App\Dto\TransferTransactionDto;
use App\InputFilter\TransferTransactionInputFilter;
use App\Service\TransactionsService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use App\Exception\NotEnoughMoneyException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\Translator;

class TransferTransactionHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TransactionsService $service,
        private readonly TransferTransactionInputFilter $inputFilter,
        private readonly Translator $translator,
    ) {}


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
            $this->service->transferMoney(TransferTransactionDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], StatusCodeInterface::STATUS_NOT_FOUND);
        } catch(NotEnoughMoneyException $e) {
            return new JsonResponse(['error' => $this->translator->translate("accountNotEnoughMoney")], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
