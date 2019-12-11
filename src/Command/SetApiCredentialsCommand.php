<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Service\ConfigurationService;
use TYPO3\CrowdinBridge\Service\InfoService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetApiCredentialsCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:api:add')
            ->setDescription('Set API credentials')
            ->addArgument('project', InputArgument::REQUIRED, 'Project Identifier')
            ->addArgument('key', InputArgument::REQUIRED, 'Key')
            ->addArgument('extensionKey', InputArgument::REQUIRED, 'Extension Key');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectIdentifier = $input->getArgument('project');
        $io = new SymfonyStyle($input, $output);

        $apiCredentialsService = new ConfigurationService('');
        $apiCredentialsService->add($projectIdentifier, $input->getArgument('key'), $input->getArgument('extensionKey'));

        $io->success('API credentials have been successfully set!');
        $io->caution('However... hold on and wait for a 1st test!');

        $infoService = new InfoService($projectIdentifier);
        try {
            $data = $infoService->get();
            $data->getContents();
            $io->success('Yes it works!');
        } catch (\Exception $e) {
            $io->error('Sorry, seems there is a problem: ' . $e->getMessage());
        }
    }
}
