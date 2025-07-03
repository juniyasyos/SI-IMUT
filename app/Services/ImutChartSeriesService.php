<?php

namespace App\Services;

use App\Models\ImutCategory;

class ImutChartSeriesService
{
    protected array $colorThemes = [
        'modern' => ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4', '#eab308', '#ef4444', '#0ea5e9', '#22c55e'],
    ];

    public function getDefaultColors(): array
    {
        return $this->colorThemes['modern'];
    }

    public function getCategories(): array
    {
        return ImutCategory::orderBy('short_name')->pluck('short_name')->toArray();
    }

    public function buildSeries($laporans, ?array $formData): array
    {
        $categories = $this->getCategories();
        $dataPerKategori = $this->calculateAchievementData(collect($laporans), $categories);
        $colors = $this->getDefaultColors();

        return collect($categories)->map(function ($shortName, $i) use ($formData, $dataPerKategori) {
            return [
                'name' => $shortName,
                'type' => $formData['series_types'][$shortName] ?? 'column',
                'data' => $dataPerKategori[$shortName] ?? [],
                'color' => $formData['series_colors'][$shortName]
                    ?? $this->getDefaultColors()[$i % count($this->getDefaultColors())],
            ];
        })->values()->toArray();
    }

    public function calculateAchievementData($laporans, array $categories): array
    {
        $data = [];
        foreach ($categories as $shortName) {
            $data[$shortName] = array_fill(0, $laporans->count(), 0);
        }

        foreach ($laporans as $i => $laporan) {
            foreach ($laporan->laporanUnitKerjas as $unitKerja) {
                foreach ($unitKerja->imutPenilaians as $penilaian) {
                    $profile = $penilaian->profile;
                    $category = $profile?->imutData?->categories;

                    if (! $category || ! $category->short_name || $penilaian->denominator_value == 0) {
                        continue;
                    }

                    $shortName = $category->short_name;
                    $nilai = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;

                    if ($nilai >= $profile->target_value) {
                        $data[$shortName][$i]++;
                    }
                }
            }
        }

        return $data;
    }
}