<?php

namespace App\Services\Usage;

use App\Models\Company;
use App\Models\UsageRecord;
use Carbon\CarbonInterface;

/**
 * Record a unit of platform usage for a company.
 */
class RecordUsageService
{
    /**
     * Create a usage record.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        Company $company,
        string $type,
        int $quantity = 1,
        ?float $unitPrice = null,
        ?float $totalPrice = null,
        string $currency = 'KES',
        ?int $referenceId = null,
        CarbonInterface|string|null $usageDate = null,
        array $metadata = []
    ): UsageRecord {
        if ($totalPrice === null && $unitPrice !== null) {
            $totalPrice = $unitPrice * $quantity;
        }

        return UsageRecord::create([
            'company_id' => $company->id,
            'type' => $type,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'usage_date' => $usageDate ?? now()->toDateString(),
            'metadata' => $metadata,
        ]);
    }
}
