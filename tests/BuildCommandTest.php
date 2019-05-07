<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo\Tests;

use CreativeCommoners\CreateSSDemo\BuildCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class BuildCommandTest extends TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Process&MockObject
     */
    protected $process;

    /**
     * @var BuildCommand&MockObject
     */
    protected $command;

    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application();

        $this->process = $this->createMock(Process::class);

        $this->command = $this->getMockBuilder(BuildCommand::class)
            ->setConstructorArgs(['build'])
            ->setMethods(['getProcess'])
            ->getMock();
        $this->command->expects($this->any())->method('getProcess')->willReturn($this->process);

        $this->application->add($this->command);
    }

    public function testExecute()
    {
        $this->process->expects($this->any())->method('isSuccessful')->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => 'build']);

        $output = $commandTester->getDisplay();
        $this->assertContains('Image build successful', $output);
    }

    public function testExecuteShowsError()
    {
        $this->process->expects($this->any())->method('isSuccessful')->willReturn(false);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => 'build']);

        $output = $commandTester->getDisplay();
        $this->assertContains('Something went wrong!', $output);
    }
}
