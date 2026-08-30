<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\Usage\RecordUsageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $companyId,
        public string $type,
        public int $quantity = 1,
        public ?float $unitPrice = null,
        public ?float $totalPrice = null,
        public string $currency = 'KES',
        public ?int $referenceId = null,
        public ?string $usageDate = null,
        public array $metadata = []
    ) {}

    public function handle(RecordUsageService $service): void
    {
        $company = Company::query()->find($this->companyId);

        if (! $company) {
            $this->delete();

            return;
        }

        $service->handle(
            $company,
            $this->type,
            $this->quantity,
            $this->unitPrice,
            $this->totalPrice,
            $this->currency,
            $this->referenceId,
            $this->usageDate,
            $this->metadata
        );
    }
}
