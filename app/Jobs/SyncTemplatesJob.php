<?php

namespace App\Jobs;

use App\Services\WhatsApp\Templates\SyncTemplatesService;
use App\Models\WhatsAppAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Pull a WABA's templates from Meta into the database.
 */
class SyncTemplatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120];

    public function __construct(
        public WhatsAppAccount $account
    ) {}

    public function handle(SyncTemplatesService $service): void
    {
        $service->handle($this->account);
    }
}