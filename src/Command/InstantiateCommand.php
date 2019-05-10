<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo\Command;

use CreativeCommoners\CreateSSDemo\Service\SSPClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Requests a demo creation from SilverStripe Platform's API using the given Docker image, version, and content
 * snapshot ID, then outputs the results to the console.
 */
class InstantiateCommand extends Command
{
    protected static $defaultName = 'instantiate';

    protected function configure()
    {
        $this
            ->addArgument(
                'site_name',
                InputArgument::REQUIRED,
                'The demo site name, e.g. john-sprint-demo (lowercase with dashes only)'
            )
            ->addArgument(
                'image',
                InputArgument::REQUIRED,
                'Docker image and version to use e.g. johnsmith/sprint-2:01'
            )
            ->addArgument(
                'stack_name',
                InputArgument::REQUIRED,
                'SilverStripe Platform stack code e.g. mystack'
            )
            ->addArgument(
                'snapshot_id',
                InputArgument::REQUIRED,
                'Content snapshot ID to use (from SilverStripe Platform stack)'
            );

        $this->setDescription(
            'Requests a demo site to be created using the given Docker image, stack name, and content'
            . ' snapshot ID'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteName = $input->getArgument('site_name');
        $image = $input->getArgument('image');
        $stackName = $input->getArgument('stack_name');
        $snapshotId = (int)$input->getArgument('snapshot_id');

        $client = $this->getClient();

        // Request to build new site
        $demoId = $client->instantiate($siteName, $image, $stackName, $snapshotId);
        if (!$demoId) {
            $output->writeln('<error>Failed to register demo site build!</error>');
            return;
        }
        $output->writeln('<comment>Build request queued. Waiting for demo to be ready.</comment>');

        while (true) {
            $status = $client->status($stackName, $demoId);
            if (empty($status['data']['attributes']['status'])) {
                $output->writeln([
                    '',
                    '<error>Failed to fetch demo status for #' . $demoId . '. Exiting...</error>',
                ]);
                return;
            }

            $buildStatus = $status['data']['attributes']['status'];
            if (in_array($buildStatus, ['Queued', 'Started'])) {
                $output->write('.');
            }

            if ($buildStatus === 'Failed') {
                $output->writeln([
                    '',
                    '<error>Failure building demo #' . $demoId . '!</error>',
                    $status['data']['attributes']['failure_message'] ?? json_encode($status),
                ]);
                return;
            }

            if ($buildStatus === 'Finished') {
                $output->writeln('');
                break;
            }

            // Poll time before checking again
            $this->sleep();
        }

        // Output results to screen
        $output->writeln('<info>Demo built successfully:</info>');

        $table = new Table($output);
        $table->setRows([
            ['Demo ID', $status['data']['id']],
            ['URL', $status['data']['attributes']['endpoint']],
            ['Username', $status['data']['attributes']['admin_username']],
            ['Password', $status['data']['attributes']['admin_password']],
        ]);
        $table->render();
    }

    /**
     * @return SSPClient
     */
    protected function getClient(): SSPClient
    {
        return new SSPClient();
    }

    /**
     * Sleeps for a certain period of time. Used mainly for status request poll intervals.
     */
    protected function sleep(): void
    {
        sleep(10);
    }
}
