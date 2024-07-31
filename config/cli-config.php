<?php
declare(strict_types=1);

$container = require 'config/container.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet(
    $container->get('doctrine.entity_manager.orm_default')
);