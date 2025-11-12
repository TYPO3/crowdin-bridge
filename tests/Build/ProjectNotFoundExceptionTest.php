<?php

declare(strict_types=1);


namespace App\Tests\Build;

use App\Build\ProjectNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectNotFoundException::class)]
final class ProjectNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function fromProjectIdentifier(): void
    {
        $actual = ProjectNotFoundException::fromProjectIdentifier('some-project-identifier');

        self::assertSame('Project "some-project-identifier" does not exist. Remember: The project name has to start with "typo3-extension-"', $actual->getMessage());
        self::assertSame(1760703485, $actual->getCode());
    }
}
