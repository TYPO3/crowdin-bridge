<?php

declare(strict_types=1);


namespace App\Tests\Command;

use App\Build\Builder;
use App\Build\ProjectNotFoundException;
use App\Command\BuildCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(BuildCommand::class)]
final class BuildCommandTest extends TestCase
{
    private MockObject $builderMock;

    protected function setUp(): void
    {
        $this->builderMock = self::createMock(Builder::class);

        $command = new BuildCommand($this->builderMock);
        $this->commandTester = new CommandTester($command);
    }

    #[Test]
    public function callingBuilderWithInvalidProjectIdentifierReturnsFailure(): void
    {
        $projectIdentifier = 'invalid-identifier';

        $this->builderMock
            ->method('build')
            ->with($projectIdentifier, self::anything())
            ->willThrowException(ProjectNotFoundException::fromProjectIdentifier($projectIdentifier));

        $this->commandTester->execute(['project' => $projectIdentifier]);

        self::assertStringContainsString('[ERROR] Project "invalid-identifier" does not exist', $this->commandTester->getDisplay());
        self::assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function callingBuilderWithValidProjectIdentifierReturnsSuccess(): void
    {
        $projectIdentifier = 'valid-identifier';

        $this->builderMock
            ->method('build')
            ->with($projectIdentifier, self::anything());

        $this->commandTester->execute(['project' => $projectIdentifier]);

        self::assertStringContainsString('[OK] Project "valid-identifier" has been successfully built', $this->commandTester->getDisplay());
        $this->commandTester->assertCommandIsSuccessful();
    }

    #[Test]
    public function callingBuilderForAllProjectsReturnsSuccessInNonVerboseMode(): void
    {
        $this->builderMock
            ->method('build')
            ->with('', self::anything());

        $this->commandTester->execute([]);

        self::assertStringContainsString('[OK] All projects have been successfully built', $this->commandTester->getDisplay());
        $this->commandTester->assertCommandIsSuccessful();
    }
}
