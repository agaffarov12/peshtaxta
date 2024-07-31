<?php
declare(strict_types=1);

namespace App\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtVerificationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $key)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizationHeader = $request->getHeader('Authorization')[0] ?? null;

        if (!$authorizationHeader || !preg_match("/^\s*Bearer\s+(\S+)\s*$/i", $authorizationHeader)) {
            return $this->generateJsonErrorResponse(
                "Token is invalid or not found",
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $token = JWT::decode($this->extractToken($authorizationHeader), new Key($this->key, 'RS256'));
        } catch(\UnexpectedValueException $e) {
            return $this->generateJsonErrorResponse($e->getMessage(), StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        return $handler->handle($request);
    }

    private function generateJsonErrorResponse(string $message, int $code): JsonResponse
    {
        return new JsonResponse(
            ['error' => ['code' => $code, 'message' => $message]],
            $code
        );
    }

    private function extractToken(string $header): string
    {
        $arr = explode(" ", $header);

        return $arr[1];
    }

}
