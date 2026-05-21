<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum FeedReportDecision: string
{
    case Rejected = 'rejected';
    case ContentRemoved = 'content_removed';
}
