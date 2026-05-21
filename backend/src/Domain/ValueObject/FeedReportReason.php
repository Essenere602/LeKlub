<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum FeedReportReason: string
{
    case Spam = 'spam';
    case Insults = 'insults';
    case Harassment = 'harassment';
    case HateContent = 'hate_content';
    case InappropriateContent = 'inappropriate_content';
    case Other = 'other';
}
