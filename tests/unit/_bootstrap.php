<?php

include_once __DIR__ . '/../TestSitePath/autoloader.php';
include_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../vendor/kiksaus/kikcms-core/src/functions.php';

setlocale(LC_ALL, 'nl_NL');

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("Helpers\\", dirname(__DIR__) . '/Helpers/', true);
$classLoader->addPsr4("Forms\\", dirname(__DIR__) . '/Forms/', true);
$classLoader->addPsr4("Models\\", dirname(__DIR__) . '/Models/', true);
$classLoader->addPsr4("DataTables\\", dirname(__DIR__) . '/DataTables/', true);
$classLoader->register();