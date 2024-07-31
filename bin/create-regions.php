<?php
declare(strict_types=1);

use App\Service\RegionsService;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var RegionsService $regionsService */
$regionsService = $container->get(RegionsService::class);

$regionsService->createRegionsFromFile("./data/regions.json");
