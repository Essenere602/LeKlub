<?php

declare(strict_types=1);

namespace App\Tests\DTO\Feed;

use App\DTO\Feed\CreateCommentRequest;
use App\DTO\Feed\CreatePostRequest;
use PHPUnit\Framework\TestCase;

final class CreatePostRequestTest extends TestCase
{
    public function testItNormalizesPostContentAsPlainText(): void
    {
        $request = CreatePostRequest::fromArray([
            'content' => '  <strong>Hello LeKlub</strong>  ',
        ]);

        self::assertSame('Hello LeKlub', $request->content);
    }

    public function testItNormalizesCommentContentAsPlainText(): void
    {
        $request = CreateCommentRequest::fromArray([
            'content' => '  <script>alert(1)</script> Beau match  ',
        ]);

        self::assertSame('alert(1) Beau match', $request->content);
    }
}
