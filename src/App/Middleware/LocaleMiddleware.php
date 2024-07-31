<?php
declare(strict_types=1);

namespace App\Middleware;

use Laminas\I18n\Translator\Translator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Translator $translator)
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getHeader('locale')[0] ?? 'uz';

        if ($locale === 'uz') {
            $locale = "uz_UZ";
        } else {
            $locale = "ru_RU";
        }

        $this->translator->setLocale($locale);

        return $handler->handle($request);
    }
}