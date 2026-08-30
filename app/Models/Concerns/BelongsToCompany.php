<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Adds company ownership to a tenant model.
 *
 * - Auto-fills company_id on create from the CompanyContext when not set.
 * - Provides a `forCompany()` query scope for explicit filtering.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            $companyId = app(CompanyContext::class)->id();

            if (empty($model->company_id) && $companyId !== null) {
                $model->company_id = $companyId;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, int|Company $company): Builder
    {
        $companyId = $company instanceof Company ? $company->getKey() : $company;

        return $query->where($query->getModel()->getTable().'.company_id', $companyId);
    }
}
