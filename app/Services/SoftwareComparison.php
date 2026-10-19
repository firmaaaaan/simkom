<?php

namespace App\Services;

use App\Models\PcSoftware;
use App\Models\Software;
use Illuminate\Support\Collection;

/**
 * Pencocokan nama software hasil scan agent (pc_softwares, nama registry
 * Windows) dengan catalog software (software.name, nama "manusia").
 *
 * Strategi: normalisasi lowercase + buang non-alfanumerik, lalu pencocokan
 * dua arah (exact atau substring) dengan prioritas kandidat terpanjang —
 * "Microsoft Visual Studio Code 1.85" cocok dengan catalog "Visual Studio
 * Code", tanpa mengaktifkan match pendek yang ambigu.
 */
class SoftwareComparison
{
    /** Panjang normalisasi minimum supaya match dianggap berarti. */
    private const MIN_NORMALIZED = 4;

    /** Normalisasi teks: huruf kecil, buang karakter non-alfanumerik. */
    public static function normalize(string $text): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '', $text));
    }

    /**
     * Bandingkan catalog software (spesifikasi) dengan hasil scan agent.
     *
     * @param  Collection<int, Software>  $catalog
     * @param  Collection<int, PcSoftware>|null  $detected
     * @return array{matched: array<int, array{catalog: string, detected: string, version: ?string}>, extra: array<int, array{name: string, version: ?string}>, missing: array<int, string>}
     */
    public static function compare(Collection $catalog, ?Collection $detected): array
    {
        $detected = $detected ?? collect();

        $detectedNormalized = $detected
            ->map(fn ($item) => [
                'item' => $item,
                'normalized' => self::normalize((string) $item->name),
            ])
            ->filter(fn ($row) => strlen($row['normalized']) >= self::MIN_NORMALIZED)
            ->values();

        $matchedDetectedIndexes = [];
        $matched = [];
        $missing = [];

        // Urutkan catalog dari nama terpanjang: match spesifik menang lebih dulu.
        $catalogSorted = $catalog
            ->sortByDesc(fn ($software) => strlen(self::normalize((string) $software->name)))
            ->values();

        foreach ($catalogSorted as $software) {
            $normalizedCatalog = self::normalize((string) $software->name);

            if ($normalizedCatalog === '' || strlen($normalizedCatalog) < self::MIN_NORMALIZED) {
                $missing[] = (string) $software->name;

                continue;
            }

            $best = null;
            $bestLength = 0;

            foreach ($detectedNormalized as $index => $row) {
                if (in_array($index, $matchedDetectedIndexes, true)) {
                    continue;
                }

                $normalizedDetected = $row['normalized'];

                $exact = $normalizedDetected === $normalizedCatalog;
                $contains = str_contains($normalizedDetected, $normalizedCatalog)
                    || str_contains($normalizedCatalog, $normalizedDetected);

                if (! $exact && ! $contains) {
                    continue;
                }

                $length = min(strlen($normalizedDetected), strlen($normalizedCatalog));
                if ($length > $bestLength) {
                    $best = $row;
                    $bestLength = $length;
                }
            }

            if ($best) {
                $matchedDetectedIndexes[] = array_search($best, $detectedNormalized->all(), true);
                $matched[] = [
                    'catalog' => (string) $software->name,
                    'detected' => (string) $best['item']->name,
                    'version' => $best['item']->version ?? null,
                ];
            } else {
                $missing[] = (string) $software->name;
            }
        }

        $extra = $detectedNormalized
            ->reject(fn ($row, $index) => in_array($index, $matchedDetectedIndexes, true))
            ->map(fn ($row) => [
                'name' => (string) $row['item']->name,
                'version' => $row['item']->version ?? null,
            ])
            ->values()
            ->all();

        sort($missing);

        return [
            'matched' => $matched,
            'extra' => $extra,
            'missing' => $missing,
        ];
    }
}
