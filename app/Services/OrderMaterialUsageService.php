<?php

namespace App\Services;

class OrderMaterialUsageService
{
    public function calculate(
        float $qty,
        ?float $lengthCm,
        ?float $widthCm,
        ?float $rollWidthCm = null,
        ?float $wastePercent = null
    ): float {
        if ($qty <= 0) {
            return 0;
        }

        if (!$lengthCm || !$widthCm) {
            return $qty;
        }

        $flatAreaM2 = ($lengthCm / 100) * ($widthCm / 100) * $qty;
        $normalizedWastePercent = $this->normalizeWastePercent($wastePercent);

        if (!$rollWidthCm || $rollWidthCm <= 0) {
            return $this->applyWaste($flatAreaM2, $normalizedWastePercent);
        }

        $acrossNormal = (int) floor($rollWidthCm / $widthCm);
        $acrossRotated = (int) floor($rollWidthCm / $lengthCm);
        $bestAcross = max($acrossNormal, $acrossRotated);

        if ($bestAcross < 1) {
            return $this->applyWaste($flatAreaM2, $normalizedWastePercent);
        }

        $runLengthCm = $acrossRotated > $acrossNormal ? $widthCm : $lengthCm;
        $totalRuns = (int) ceil($qty / $bestAcross);
        $rollConsumedAreaM2 = ($totalRuns * $runLengthCm * $rollWidthCm) / 10000;

        // Never consume less than plain area.
        $baseUsageM2 = max($flatAreaM2, $rollConsumedAreaM2);

        return $this->applyWaste($baseUsageM2, $normalizedWastePercent);
    }

    protected function normalizeWastePercent(?float $wastePercent): float
    {
        $value = (float) ($wastePercent ?? 0);

        if ($value < 0) {
            return 0;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }

    protected function applyWaste(float $baseUsageM2, float $wastePercent): float
    {
        return round($baseUsageM2 * (1 + ($wastePercent / 100)), 4);
    }
}
