<?php

namespace App\Support;

use App\Models\Company;

/**
 * Request-scoped holder for the company a request is acting on.
 *
 * Set by the API-key auth middleware (and by web controllers when a
 * company is resolved); consumed by the BelongsToCompany trait and
 * the CompanyScope so tenant filtering is consistent everywhere.
 */
class CompanyContext
{
    protected ?Company $company = null;

    public function set(?Company $company): void
    {
        $this->company = $company;
    }

    public function get(): ?Company
    {
        return $this->company;
    }

    public function id(): ?int
    {
        return $this->company?->getKey();
    }

    public function has(): bool
    {
        return $this->company !== null;
    }

    public function forget(): void
    {
        $this->company = null;
    }
}
