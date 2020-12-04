<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');

$app = new \Symfony\Component\Console\Application('Crowdin-TYPO3 Bridge', '2.0');

$app->add(new \TYPO3\CrowdinBridge\Command\SetupCommand());

$app->add(new \TYPO3\CrowdinBridge\Command\BuildCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\ExtractExtensionCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\ExtractCoreCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\StatusCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\StatusExportCommand());


$app->add(new \TYPO3\CrowdinBridge\Command\Management\StatusCommand());

$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaBuildCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaExtractExtensionsCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaStatusExportCommand());
$app->run();
