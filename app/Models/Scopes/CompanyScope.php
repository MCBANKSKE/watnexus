<?php

namespace App\Models\Scopes;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Resolution order:
     *  1. CompanyContext (set by API-key auth middleware).
     *  2. Authenticated user's active company inside the admin panel.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $contextCompany = app(CompanyContext::class)->get();

        if ($contextCompany) {
            $builder->where(
                $model->getTable().'.company_id',
                $contextCompany->getKey()
            );

            return;
        }

        if (! $this->isAdminPanel()) {
            return;
        }

        $user = Auth::user();

        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        $companyId = $user->companies()
            ->wherePivot('is_active', true)
            ->value('companies.id');

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }

    /**
     * Check if current request is for the admin panel.
     */
    protected function isAdminPanel(): bool
    {
        $currentPanel = filament()->getCurrentPanel()?->getId();

        return $currentPanel === 'admin';
    }
}
