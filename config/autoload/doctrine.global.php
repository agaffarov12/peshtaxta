<?php
declare(strict_types=1);

use App\Doctrine\BookingIdType;
use App\Doctrine\CampaignIdType;
use App\Doctrine\ClientIdType;
use App\Doctrine\CreativeIdType;
use App\Doctrine\OrderIdType;
use App\Doctrine\PaymentIdType;
use App\Doctrine\ProductIdType;
use App\Doctrine\ProductPlacementIdType;
use App\Doctrine\ServiceIdType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Ramsey\Uuid\Doctrine\UuidType;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

return [
    'doctrine'     => [
        'driver'        => [
            'orm_default' => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'App\\'      => 'entities',
                    'Product\\'  => 'entities',
                    'Common\\'   => 'entities',
                    'Campaign\\' => 'entities',
                    'Money\\'    => 'xml_driver',
                ],
            ],
            'entities'    => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../../src/App',
                    __DIR__ . '/../../src/Product',
                    __DIR__ . '/../../src/Common',
                    __DIR__ . '/../../src/Campaign'
                ],
            ],
            'xml_driver'  => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../data/doctrine/types',
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'naming_strategy' => UnderscoreNamingStrategy::class,
            ],
        ],
        'types'         => [
            UuidType::NAME               => UuidType::class,
            ProductIdType::NAME          => ProductIdType::class,
            BookingIdType::NAME          => BookingIdType::class,
            ClientIdType::NAME           => ClientIdType::class,
            CampaignIdType::NAME         => CampaignIdType::class,
            OrderIdType::NAME            => OrderIdType::class,
            PaymentIdType::NAME          => PaymentIdType::class,
            CreativeIdType::NAME         => CreativeIdType::class,
            ServiceIdType::NAME          => ServiceIdType::class,
            ProductPlacementIdType::NAME => ProductPlacementIdType::class,
        ],
    ],
    'dependencies' => [
        'aliases'   => [
            'doctrine.entity_manager.orm_default' => EntityManagerInterface::class,
        ],
        'factories' => [
            EntityManagerInterface::class   => EntityManagerFactory::class,
            UnderscoreNamingStrategy::class => InvokableFactory::class
        ],
    ],
];
