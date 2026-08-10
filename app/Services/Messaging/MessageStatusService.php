<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Models\MessageStatus;

/**
 * Apply WhatsApp delivery/read status changes to messages
 * while preserving a history in message_statuses.
 */
class MessageStatusService
{
    /**
     * Valid lifecycle per target status. Out-of-order transitions
     * (e.g. "read" before "delivered") are ignored.
     *
     * @var array<string, list<string>>
     */
    protected const PROGRESSION = [
        'queued' => ['queued'],
        'sending' => ['queued', 'sending'],
        'sent' => ['queued', 'sending', 'sent'],
        'delivered' => ['queued', 'sending', 'sent', 'delivered'],
        'read' => ['queued', 'sending', 'sent', 'delivered', 'read'],
        'failed' => ['queued', 'sending', 'sent', 'delivered', 'read', 'failed'],
    ];

    /**
     * Valid statuses for the webhook updates only.
     *
     * @var list<string>
     */
    protected const VALID_STATUSES = [
        'queued',
        'sending',
        'sent',
        'delivered',
        'read',
        'failed',
    ];

    /**
     * Transition a message to a new status (and record it).
     */
    public function applyStatus(
        Message $message,
        string $status,
        ?string $whatsappMessageId = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            return false;
        }

        // Refuse invalid regressions (unless retrying a failed message).
        if ($message->status !== null
            && $message->status !== 'failed'
            && !in_array($message->status, self::PROGRESSION[$status], true)) {
            return false;
        }

        $timestampColumn = $this->timestampColumn($status);

        $message->updateQuietly([
            'status' => $status,
            'whatsapp_message_id' => $whatsappMessageId
                ?: $message->whatsapp_message_id,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            $timestampColumn => now(),
        ]);

        MessageStatus::create([
            'message_id' => $message->id,
            'whatsapp_message_id' => $whatsappMessageId
                ?: $message->whatsapp_message_id,
            'status' => $status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'occurred_at' => now(),
            'metadata' => $metadata,
        ]);

        return true;
    }

    /**
     * Map a status to a timestamp column on the messages table.
     */
    protected function timestampColumn(string $status): ?string
    {
        return match ($status) {
            'sent' => 'sent_at',
            'delivered' => 'delivered_at',
            'read' => 'read_at',
            'failed' => 'failed_at',
            default => null,
        };
    }
}