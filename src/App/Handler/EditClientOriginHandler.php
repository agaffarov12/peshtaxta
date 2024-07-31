<?php
declare(strict_types=1);

namespace App\Handler;

use App\InputFilter\CategoryInputFilter;
use App\Service\ClientOriginService;
use App\Utils\Arrays;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EditClientOriginHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ClientOriginService $service,
        private readonly CategoryInputFilter $inputFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = Arrays::columns(
            array_merge_recursive($request->getAttributes(), $request->getParsedBody()),
            ['id', 'name']
        );

        $this->inputFilter->setValidationGroup(["id", "name"]);
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $values = $this->inputFilter->getValues();

        try {
            $this->service->edit($values['id'], $values['name']);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => "Entity not found"], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
