<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\I18n\Translator\Loader\PhpArray;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TranslatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Translator
    {
        $languages  = $container->get('config')['translator']['languages'];
        $translator = new Translator();

        foreach ($languages as $lang) {
            $translator->addTranslationFile(PhpArray::class, $lang['path'], 'default', $lang['locale']);
        }

        return $translator;
    }
}