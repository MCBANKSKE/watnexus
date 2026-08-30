<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Watch a running campaign and finalize it once every recipient
 * message has reached a terminal status (sent / delivered / read /
 * failed). Re-schedules itself with a delay while messages are
 * still in flight.
 */
class FinalizeCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 30;

    public int $timeout = 60;

    /**
     * Longest we keep polling before force-completing (in seconds).
     */
    protected const MAX_AGE_SECONDS = 86400;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(): void
    {
        // Campaign may have been deleted while polling.
        if (! $this->campaign->exists) {
            return;
        }

        if ($this->campaign->isCompleted()) {
            return;
        }

        $pending = $this->campaign->messages()
            ->whereIn('messages.status', ['queued', 'sending'])
            ->count();

        $expired = $this->campaign->started_at !== null
            && $this->campaign->started_at->diffInSeconds(now()) > self::MAX_AGE_SECONDS;

        if ($pending === 0 || $expired) {
            $this->campaign->refresh();

            $this->campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return;
        }

        // Still in flight — check again shortly.
        $this->release(60);
    }

    public function failed(Throwable $e): void
    {
        if ($this->campaign->exists && ! $this->campaign->isCompleted()) {
            $this->campaign->updateQuietly([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge($this->campaign->metadata ?? [], [
                    'finalize_error' => $e->getMessage(),
                ]),
            ]);
        }
    }
}
