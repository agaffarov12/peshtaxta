<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\ProductDto;
use App\InputFilter\ProductInputFilter;
use App\Service\ProductsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateProductHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ProductsService $service,
        private readonly ProductInputFilter $inputFilter
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $this->orderPlacements($files, $body);
        $body['files'] = $files['files'] ?? null;

        $this->inputFilter->setData($body);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $dto = ProductDto::fromArray($this->inputFilter->getValues());

        try {
            $this->service->create($dto);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                [
                    'messages' => ['entity' => "Category not found"],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        return new JsonResponse("ok");
    }

    private function orderPlacements(array &$files, &$input): void
    {
        if (!array_key_exists("placements", $files) || !isset($files['placements'])) {
            return;
        }

        for ($i = 0; $i < count($files['placements']); $i++) {
            if (!isset($files['placements'][$i]['images'])) {
                continue;
            }

            $input['placements'][$i]['images'] = $files['placements'][$i]['images'];
        }

        unset($files['placements']);
    }
}
