<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope in admin panel
        if (!$this->isAdminPanel()) {
            return;
        }

        // Only apply if user is authenticated
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Filter by user's company_id
        if ($user->company_id) {
            $builder->where($model->getTable() . '.company_id', $user->company_id);
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
