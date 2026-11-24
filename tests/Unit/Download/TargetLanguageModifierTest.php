<?php

declare(strict_types=1);

namespace App\Tests\Unit\Download;

use App\Download\TargetLanguageModifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[CoversClass(TargetLanguageModifier::class)]
final class TargetLanguageModifierTest extends TestCase
{
    #[Test]
    public function modifyReturnsTrueIfOriginalLanguageIsTheSameAsTypo3Language(): void
    {
        $subject = new TargetLanguageModifier(new NullLogger());

        $actual = $subject->modify('some-file', 'de', 'de');

        self::assertTrue($actual);
    }

    #[Test]
    public function modifyReturnsFalseIfFileIsNotAFile(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $folderPath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        mkdir($folderPath);

        $actual = $subject->modify($folderPath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('File "' . $folderPath . '" is not a file', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyReturnsFalseIfContentOfFileCannotBeRead(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        touch($filePath);
        chmod($filePath, 0333);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('Content from file "' . $filePath . '" cannot be read', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyModifiesTheTargetLanguageCorrectlyAndReturnsTrue(): void
    {
        $subject = new TargetLanguageModifier(new NullLogger());

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.locallang.xlf';
        $expectedFilePath = __DIR__ . '/Fixtures/sv.locallang.expected.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertTrue($actual);
        self::assertFileEquals($expectedFilePath, $filePath);
    }

    #[Test]
    public function modifyReturnsFalseIfContentCannotBeWrittenToFile(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.locallang.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);
        chmod($filePath, 0444);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('Content cannot be written to file "' . $filePath . '"', $loggerStub->warningMessage);
    }

    private function getLoggerStub(): LoggerInterface
    {
        return new class implements LoggerInterface {
            public string $warningMessage = '';

            public function emergency(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function alert(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function critical(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function error(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function warning(\Stringable|string $message, array $context = []): void
            {
                $this->warningMessage = $message;
            }

            public function notice(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function info(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function debug(\Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                throw new \Exception('Not used');
            }
        };
    }
}
