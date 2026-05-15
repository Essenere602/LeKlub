<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class FootballCompetition
{
    private const COMPETITIONS = [
        'FL1' => ['name' => 'Ligue 1', 'country' => 'France'],
        'PL' => ['name' => 'Premier League', 'country' => 'Angleterre'],
        'PD' => ['name' => 'LaLiga', 'country' => 'Espagne'],
        'SA' => ['name' => 'Serie A', 'country' => 'Italie'],
        'BL1' => ['name' => 'Bundesliga', 'country' => 'Allemagne'],
    ];

    /**
     * @return list<array{code: string, name: string, country: string}>
     */
    public static function all(): array
    {
        $competitions = [];

        foreach (self::COMPETITIONS as $code => $competition) {
            $competitions[] = [
                'code' => $code,
                'name' => $competition['name'],
                'country' => $competition['country'],
            ];
        }

        return $competitions;
    }

    public static function isSupported(string $code): bool
    {
        return array_key_exists(strtoupper($code), self::COMPETITIONS);
    }

    /**
     * @return array{code: string, name: string, country: string}|null
     */
    public static function find(string $code): ?array
    {
        $code = strtoupper($code);

        if (!self::isSupported($code)) {
            return null;
        }

        return [
            'code' => $code,
            'name' => self::COMPETITIONS[$code]['name'],
            'country' => self::COMPETITIONS[$code]['country'],
        ];
    }
}
