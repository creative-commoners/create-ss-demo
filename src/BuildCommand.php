<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
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
        if (!$this->copyDockerTemplates($output)) {
            return;
        }
        if (!$this->buildDockerImage($output)) {
            return;
        }
        if(!$this->tagDockerImage($output)) {
            return;
        }
        if (!$this->removeDockerTemplates($output)) {
            return;
        }
        $output->writeln('<info>All finished.</info>');
    }

    protected function getProcess(...$args): Process
    {
        $process = new Process(...$args);
        $process->setTimeout(null);
        return $process;
    }

    protected function getFilesystem(): Filesystem
    {
        return new Filesystem();
    }

    protected function buildDockerImage(OutputInterface $output): bool
    {
        // todo argument
        $name = 'tbc-demo';

        $output->writeln('<comment>Building main Docker image</comment>: ' . $name);
        $process = $this->getProcess(['docker', 'build', '-t', $name, '.']);
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::OUT === $type) {
                // stdout
                return $output->writeln($buffer);
            }
            // stderr
            return $output->writeln('<error>ERROR: </error> ' . $buffer);
        });

        if (!$process->isSuccessful()) {
            $output->writeln('<error>Something went wrong!</error>');
            return false;
        }

        $output->writeln('<info>Image build successful</info>');
        return true;
    }

    protected function tagDockerImage(OutputInterface $output): bool
    {
        // todo argument
        $name = 'tbc-demo';
        $user = 'robbieaverill';
        $version = '0.2';

        $output->writeln('<comment>Getting new image ID</comment>');
        $process = $this->getProcess("docker image ls --filter reference='{$name}:latest' --format '{{.ID}}'");
        $process->run(null, ['NAME' => $name]);
        $imageId = trim((string) $process->getOutput());

        $output->writeln('<info>Build image ID:</info> ' . $imageId);

        $output->writeln('<comment>Tagging new image</comment>');
        $process = $this->getProcess('docker tag "$IMAGE_ID" "$USER"/"$NAME":"$VERSION"');
        $process->run(null, [
            'IMAGE_ID' => $imageId,
            'USER' => $user,
            'NAME' => $name,
            'VERSION' => $version,
        ]);
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Error tagging image!</error>');
            $output->writeln($process->getOutput());
            return false;
        }

        $output->writeln('<comment>Pushing tag to Docker Hub</comment>');
        $process = $this->getProcess('docker push "$USER"/"$NAME"');
        $process->run(null, ['USER' => $user, 'NAME' => $name]);
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Error pushing tag to Docker Hub!</error>');
            $output->writeln($process->getOutput());
            return false;
        }

        $output->writeln(
            '<info>' . $user . '/' . $name . ':' . $version . ' (' . $imageId . ') pushed to Docker Hub</info>'
        );
        return true;
    }

    protected function copyDockerTemplates(OutputInterface $output): bool
    {
        try {
            $this->getFilesystem()->mirror(CREATE_SS_DEMO_ROOT . '/docker', getcwd());
            $output->writeln('<comment>Docker templates copied into current directory</comment>');
        } catch (IOException $exception) {
            $output->writeln('<error>Failed to sync Docker templates:</error>');
            throw $exception;
        }
        return true;
    }

    protected function removeDockerTemplates(OutputInterface $output): bool
    {
        $output->writeln(
            '<comment>Docker templates need to be cleaned up, run `git clean -fd` if you use Git'
            . ' and have tracked everything</comment>'
        );
        return true;
    }
}
