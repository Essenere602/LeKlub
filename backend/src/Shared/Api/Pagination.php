<?php

declare(strict_types=1);

namespace App\Shared\Api;

use Symfony\Component\HttpFoundation\Request;

final class Pagination
{
    public const DEFAULT_LIMIT = 10;
    public const MAX_LIMIT = 20;

    public function __construct(
        public readonly int $page,
        public readonly int $limit,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', self::DEFAULT_LIMIT);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        return new self($page, $limit);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    /**
     * @return array<string, int>
     */
    public function metadata(int $total): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total' => $total,
            'pages' => (int) ceil($total / $this->limit),
        ];
    }
}
