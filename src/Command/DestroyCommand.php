<?php

namespace CreativeCommoners\CreateSSDemo\Command;

use CreativeCommoners\CreateSSDemo\Service\SSPClient;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Ask the SilverStripe Platform API to destroy a demo site
 */
class DestroyCommand extends Command
{
    protected static $defaultName = 'destroy';

    protected function configure()
    {
        $this
            ->addArgument(
                'site_id',
                InputArgument::REQUIRED,
                'Demo site ID from SilverStripe Platform'
            )
            ->addArgument(
                'stack_name',
                InputArgument::REQUIRED,
                'SilverStripe Platform stack code e.g. mystack'
            );

        $this->setDescription('Requests a the given demo site ID to be destroyed in SilverStripe Platform');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stackName = $input->getArgument('stack_name');
        $siteId = $input->getArgument('site_id');

        $output->writeln('Processing. Please wait...');
        try {
            $this->getClient()->destroy($stackName, $siteId);
        } catch (RequestException $exception) {
            $output->writeln('<error>Demo site ID #' . $siteId . ' failed to be destroyed!');
            throw $exception;
        }

        $output->writeln('<info>Demo site ID #' . $siteId . ' destroyed.</info>');
    }

    /**
     * @return SSPClient
     */
    protected function getClient(): SSPClient
    {
        return new SSPClient();
    }
}
