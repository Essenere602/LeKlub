<?php

declare(strict_types=1);

namespace App\Application\Football;

use App\Domain\ValueObject\FootballCompetition;

final class ListFootballCompetitionsUseCase
{
    /**
     * @return list<array{code: string, name: string, country: string}>
     */
    public function execute(): array
    {
        return FootballCompetition::all();
    }
}
