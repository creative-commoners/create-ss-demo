<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo\Tests\Command;

use CreativeCommoners\CreateSSDemo\Command\InstantiateCommand;
use CreativeCommoners\CreateSSDemo\Service\SSPClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InstantiateCommandTest extends TestCase
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
     * @var InstantiateCommand&MockObject
     */
    protected $command;

    /**
     * @var array
     */
    protected $mockData = [
        'command' => 'instantiate',
        'site_name' => 'testsite1',
        'image' => 'foo/bar:1.2',
        'stack_name' => 'ssstackhere',
        'snapshot_id' => 12345,
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application();

        $this->client = $this->createMock(SSPClient::class);
        $this->command = $this->getMockBuilder(InstantiateCommand::class)
            ->setConstructorArgs(['build'])
            ->setMethods(['getClient', 'sleep'])
            ->getMock();

        $this->command->expects($this->any())->method('getClient')->willReturn($this->client);
        $this->command->expects($this->any())->method('sleep')->willReturn(true);

        $this->application->add($this->command);
    }

    public function testFailsToRequestBuild()
    {
        $this->client->expects($this->once())->method('instantiate')->willReturn(0);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Failed to register demo site build!', $output);
    }

    public function testBuildWithStatusMissing()
    {
        $this->client->expects($this->once())->method('instantiate')->willReturn(123);
        $this->client->expects($this->once())->method('status')->willReturn([]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Build request queued. Waiting for demo to be ready', $output);
        $this->assertContains('Failed to fetch demo status for #123', $output);
    }

    public function testBuildWithStatusFailure()
    {
        $this->client->expects($this->once())->method('instantiate')->willReturn(123);
        $this->client->expects($this->once())->method('status')->willReturn([
            'data' => [
                'attributes' => [
                    'status' => 'Failed',
                    'failure_message' => 'Because it is mocked.',
                ],
            ],
        ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Build request queued. Waiting for demo to be ready', $output);
        $this->assertContains('Failure building demo #123', $output);
        $this->assertContains('Because it is mocked', $output);
    }

    public function testBuildSuccessfully()
    {
        $this->client->expects($this->once())->method('instantiate')->willReturn(123);
        $this->client->expects($this->once())->method('status')->willReturn([
            'data' => [
                'id' => 110,
                'attributes' => [
                    'status' => 'Finished',
                    'endpoint' => 'https://www.silverstripe.org',
                    'admin_username' => 'admin',
                    'admin_password' => 'opens3same',
                ],
            ],
        ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($this->mockData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Build request queued. Waiting for demo to be ready', $output);
        $this->assertContains('Demo built successfully', $output);
        $this->assertContains('https://www.silverstripe.org', $output, 'Site URL should be shown');
        $this->assertContains('admin', $output, 'Admin username should be shown');
        $this->assertContains('opens3same', $output, 'Admin password should be shown');
    }
}
