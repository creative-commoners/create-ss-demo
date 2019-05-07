<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Creates a Docker image from the current working directory
 */
class BuildCommand extends Command
{
    protected static $defaultName = 'build';

    protected function configure()
    {
        $this->setDescription('Builds a Docker image from the current working directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = $this->getProcess(['docker', 'ps']);
        $process->run();

        $this->copyDockerTemplates($output);

        if (!$process->isSuccessful()) {
            $output->writeln('<error>Something went wrong!</error>');
        } else {
            $output->writeln('<info>Image build successful</info>');
        }
        $output->writeln($process->getOutput());

        $this->removeDockerTemplates($output);
    }

    protected function getProcess(...$args): Process
    {
        return new Process(...$args);
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem();
    }

    protected function copyDockerTemplates(OutputInterface $output): BuildCommand
    {
        $this->getFilesystem()->mirror(__DIR__ . '/docker', getcwd());

        return $this;
    }

    protected function removeDockerTemplates(OutputInterface $output): BuildCommand
    {
        return $this;
    }
}
