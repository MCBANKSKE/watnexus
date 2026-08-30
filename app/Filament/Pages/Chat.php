<?php

namespace App\Filament\Pages;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Messaging\MarkMessageAsReadService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Throwable;
use BackedEnum;
use UnitEnum;

class Chat extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.admin.pages.chat';

    protected static ?string $navigationLabel = 'Chat';

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

   protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 5;

    #[Locked]
    public ?int $selectedConversationId = null;

    public string $message = '';

    public ?int $selectedPhoneNumberId = null;

    public ?int $selectedTemplateId = null;

    public string $filter = 'all';

    public function mount(): void
    {
        $first = $this->getConversationsQuery()->first();

        $this->selectedConversationId = $first?->id;

        if ($first) {
            $this->openConversation($first->id);
        }
    }

    public function getCompanyId(): ?int
    {
        $user = auth()->user();

        return $user?->companies()
            ->wherePivot('is_active', true)
            ->value('companies.id');
    }

    protected function getConversationsQuery()
    {
        return Conversation::query()
            ->where('company_id', $this->getCompanyId())
            ->with(['contact', 'whatsappPhoneNumber'])
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function getConversationsProperty(): Collection
    {
        return $this->getConversationsQuery()->limit(100)->get();
    }

    public function getSelectedConversationProperty(): ?Conversation
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return Conversation::query()
            ->where('company_id', $this->getCompanyId())
            ->with(['contact', 'whatsappPhoneNumber'])
            ->find($this->selectedConversationId);
    }

    public function getMessagesProperty(): Collection
    {
        $conversation = $this->selectedConversation;

        if (!$conversation) {
            return collect();
        }

        return $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->limit(200)
            ->get();
    }

    public function getConnectedPhoneNumbersProperty(): Collection
    {
        return WhatsAppPhoneNumber::query()
            ->where('company_id', $this->getCompanyId())
            ->where('status', 'connected')
            ->get();
    }

    public function getApprovedTemplatesProperty(): Collection
    {
        return \App\Models\MessageTemplate::query()
            ->where('company_id', $this->getCompanyId())
            ->where('status', 'approved')
            ->get(['id', 'name', 'language']);
    }

    public function selectConversation(int $id): void
    {
        $this->openConversation($id);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    protected function openConversation(int $id): void
    {
        $this->selectedConversationId = $id;

        $conversation = $this->selectedConversation;

        if (!$conversation || $conversation->unread_count === 0) {
            return;
        }

        // Reset the unread counter and send read receipts to Meta.
        $unread = $conversation->messages()
            ->where('direction', 'inbound')
            ->whereNull('read_at')
            ->whereNotNull('whatsapp_message_id')
            ->get();

        foreach ($unread as $inbound) {
            $phoneNumber = $conversation->whatsappPhoneNumber;

            if ($phoneNumber && $inbound->whatsapp_message_id) {
                try {
                    app(MarkMessageAsReadService::class)->handle($phoneNumber, $inbound->whatsapp_message_id);
                } catch (Throwable) {
                    // Read receipt failures must not block the UI.
                }
            }

            $inbound->forceFill(['read_at' => now()])->saveQuietly();
        }

        $conversation->forceFill(['unread_count' => 0])->saveQuietly();
    }

    public function sendMessage(): void
    {
        $conversation = $this->selectedConversation;
        $body = trim($this->message);

        if (!$conversation || $body === '') {
            return;
        }

        $phoneNumber = $this->resolvePhoneNumber();

        if (!$phoneNumber) {
            Notification::make()
                ->setTitle('No connected WhatsApp number')
                ->body('Connect and sync a WhatsApp Business Account first.')
                ->danger()
                ->send();

            return;
        }

        $message = Message::create([
            'company_id' => $conversation->company_id,
            'conversation_id' => $conversation->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $conversation->contact_id,
            'sender_id' => auth()->id(),
            'direction' => 'outbound',
            'type' => 'text',
            'status' => 'queued',
            'body' => $body,
            'queued_at' => now(),
        ]);

        SendWhatsAppMessageJob::dispatch($message, $phoneNumber);

        $this->message = '';
    }

    public function sendTemplate(): void
    {
        $conversation = $this->selectedConversation;

        if (!$conversation || !$this->selectedTemplateId) {
            return;
        }

        $template = \App\Models\MessageTemplate::query()
            ->where('company_id', $this->getCompanyId())
            ->where('status', 'approved')
            ->find($this->selectedTemplateId);

        $phoneNumber = $this->resolvePhoneNumber();

        if (!$template || !$phoneNumber) {
            return;
        }

        $message = Message::create([
            'company_id' => $conversation->company_id,
            'conversation_id' => $conversation->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $conversation->contact_id,
            'message_template_id' => $template->id,
            'sender_id' => auth()->id(),
            'direction' => 'outbound',
            'type' => 'template',
            'status' => 'queued',
            'body' => $template->body,
            'queued_at' => now(),
        ]);

        SendWhatsAppMessageJob::dispatch($message, $phoneNumber);
    }

    public function toggleConversationStatus(): void
    {
        $conversation = $this->selectedConversation;

        if (!$conversation) {
            return;
        }

        $conversation->update([
            'status' => $conversation->isOpen() ? 'closed' : 'open',
        ]);
    }

    protected function resolvePhoneNumber(): ?WhatsAppPhoneNumber
    {
        if ($this->selectedPhoneNumberId) {
            return WhatsAppPhoneNumber::query()
                ->where('company_id', $this->getCompanyId())
                ->where('status', 'connected')
                ->find($this->selectedPhoneNumberId);
        }

        return $this->connectedPhoneNumbers->first();
    }
}