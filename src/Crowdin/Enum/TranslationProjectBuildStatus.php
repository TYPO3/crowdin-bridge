<?php

declare(strict_types=1);

namespace App\Crowdin\Enum;

enum TranslationProjectBuildStatus: string
{
    case Canceled = 'canceled';
    case Created = 'created';
    case Failed = 'failed';
    case Finished = 'finished';
    case InProgress = 'inProgress';

    public function consoleMessage(string $projectIdentifier, string $error): string
    {
        $projectText = \sprintf('Project "%s"', $projectIdentifier);

        $statusText = match ($this) {
            self::Canceled,
            self::Created => \sprintf('%s has been %s', $projectText, $this->value),
            self::Failed => \sprintf('%s has failed', $projectText),
            self::Finished => \sprintf('%s is already built', $projectText),
            self::InProgress => \sprintf('%s has been built now', $projectText),
        };

        $statusTag = 'info';
        if ($error !== '') {
            $statusText .= \sprintf(' (%s)', $error);
            $statusTag = 'error';
        }

        return \sprintf('<%s>%s</%s>', $statusTag, $statusText, $statusTag);
    }
}
