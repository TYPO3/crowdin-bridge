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
        self::assertSame('Cannot open file \'' . $filePath . '\'', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyReturnsFalseIfXliffVersionCannotBeDetermined(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        file_put_contents($filePath, '<xliff/>');

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('File "' . $filePath . '" has no XLIFF version attribute', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyReturnsFalseIfXliff1HasNoTargetLanguageAttribute(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        file_put_contents($filePath, '<xliff version="1.2"><file/></xliff>');

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('File "' . $filePath . '" has errors processing XLIFF: target-language on file node not set', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyReturnsFalseIfXliff2HasNoTrgLangAttribute(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        file_put_contents($filePath, '<xliff version="2.0"/>');

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('File "' . $filePath . '" has errors processing XLIFF: trgLang on xliff node not set', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyModifiesTheTargetLanguageForXliff1CorrectlyAndReturnsTrue(): void
    {
        $subject = new TargetLanguageModifier(new NullLogger());

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.xliff1.xlf';
        $expectedFilePath = __DIR__ . '/Fixtures/sv.xliff1.expected.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertTrue($actual);
        self::assertXmlFileEqualsXmlFile($expectedFilePath, $filePath);
    }

    #[Test]
    public function modifyModifiesTheTargetLanguageForXliff20CorrectlyAndReturnsTrue(): void
    {
        $subject = new TargetLanguageModifier(new NullLogger());

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.xliff20.xlf';
        $expectedFilePath = __DIR__ . '/Fixtures/sv.xliff20.expected.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertTrue($actual);
        self::assertXmlFileEqualsXmlFile($expectedFilePath, $filePath);
    }

    #[Test]
    public function modifyModifiesTheTargetLanguageForXliff22CorrectlyAndReturnsTrue(): void
    {
        $subject = new TargetLanguageModifier(new NullLogger());

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.xliff22.xlf';
        $expectedFilePath = __DIR__ . '/Fixtures/sv.xliff22.expected.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertTrue($actual);
        self::assertXmlFileEqualsXmlFile($expectedFilePath, $filePath);
    }

    #[Test]
    public function modifyReturnsFalseIfContentCannotBeWrittenToFile(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.xliff1.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);
        chmod($filePath, 0444);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('XLIFF document cannot be written to file "' . $filePath . '"', $loggerStub->warningMessage);
    }

    #[Test]
    public function modifyReturnsFalseIfXliffVersionIsNotValid(): void
    {
        $loggerStub = $this->getLoggerStub();
        $subject = new TargetLanguageModifier($loggerStub);

        $fixtureFilePath = __DIR__ . '/Fixtures/sv.xliff9.xlf';
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-target-language-modifier-' . bin2hex(random_bytes(12));
        copy($fixtureFilePath, $filePath);

        $actual = $subject->modify($filePath, 'sv-SE', 'sv');

        self::assertFalse($actual);
        self::assertSame('File "' . $filePath . '" has errors processing XLIFF: XLIFF version "9.9" is not supported', $loggerStub->warningMessage);
    }

    private function getLoggerStub(): LoggerInterface
    {
        return new class () implements LoggerInterface {
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
