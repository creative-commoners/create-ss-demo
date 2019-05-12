<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo\Test\Command;

use CreativeCommoners\CreateSSDemo\Command\DestroyCommand;
use CreativeCommoners\CreateSSDemo\Service\SSPClient;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DestroyCommandTest extends TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var SSPClient&MockObject
     */
    protected $client;

    /**
     * @var DestroyCommand&MockObject
     */
    protected $command;

    /**
     * @var array
     */
    protected $mockData = [
        'command' => 'destroy',
        'site_id' => 12345,
        'stack_name' => 'ssstackhere',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application();

        $this->client = $this->createMock(SSPClient::class);
        $this->command = $this->getMockBuilder(DestroyCommand::class)
            ->setConstructorArgs(['destroy'])
            ->setMethods(['getClient'])
            ->getMock();

        $this->command->expects($this->any())->method('getClient')->willReturn($this->client);

        $this->application->add($this->command);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testFailedToDestroySite()
    {
        $this->client->expects($this->once())->method('destroy')->willThrowException(
            $this->createMock(RequestException::class)
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);
    }

    public function testDestroySite()
    {
        $this->client->expects($this->once())->method('destroy')->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Demo site ID #12345 destroyed', $output);
    }
}
