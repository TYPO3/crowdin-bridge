<?php

declare(strict_types=1);

namespace App\Tests\Unit\Crowdin\Dto;

use App\Crowdin\Dto\DownloadFile;
use CrowdinApiClient\Model\DownloadFile as CrowdinDownloadFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DownloadFile::class)]
final class DownloadFileTest extends TestCase
{
    #[Test]
    public function fromCrowdinDownloadFileReturnsInstanceCorrectly(): void
    {
        $url = 'https://crowdin-packages.cf-downloads.crowdin.com/90805931/368815/0/pack.zip?response-content-disposition=attachment%3B%20filename%3D%22TYPO3%20CMS.zip%22&X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAWVY4ALJ25LH4GC7S%2F20261121%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20261121T181417Z&X-Amz-SignedHeaders=host&X-Amz-Expires=120&X-Amz-Signature=26d1a5052af478021dc7603a589375bc6668315b6570a4c624aeed8f50b8150f';

        $actual = DownloadFile::fromCrowdinDownloadFile(new CrowdinDownloadFile([
            'url' => $url,
            'expireIn' => '2026-11-21T18:16:17+00:00',
        ]));

        self::assertSame($url, $actual->url->toAsciiString());
    }
}
