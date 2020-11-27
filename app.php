<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');

$app = new \Symfony\Component\Console\Application('Crowdin-TYPO3 Bridge', '2.0');
//$app->add(new \TYPO3\CrowdinBridge\Command\MessageCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\BuildCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\CrowdinExtractExtCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\CrowdinExtractCoreCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\StatusCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\StatusExportCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaBuildCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaExtractExtCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Meta\MetaStatusExportCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Management\ProjectListCommand());
$app->add(new \TYPO3\CrowdinBridge\Command\Management\StatusCommand());
$app->run();
