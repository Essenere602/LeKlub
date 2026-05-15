<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum PostReactionType: string
{
    case Like = 'like';
    case Dislike = 'dislike';
}
