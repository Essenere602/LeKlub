<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum FeedReportStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
}
