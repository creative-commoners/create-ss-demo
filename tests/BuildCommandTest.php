<?php

namespace CreativeCommoners\CreateSSDemo\Tests;

use CreativeCommoners\CreateSSDemo\BuildCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BuildCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new BuildCommand());

        $command = $application->find('build');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'build']);

        $output = $commandTester->getDisplay();
        $this->assertContains('Hello world', $output);
    }
}
