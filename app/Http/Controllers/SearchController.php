<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function companies(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $query = Company::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $companies = $query->limit(20)->get(['id', 'name', 'email']);

        return response()->json($companies->map(fn ($c) => [
            'id' => $c->id,
            'text' => $c->name . ($c->email ? " ({$c->email})" : ''),
        ])->values());
    }

    public function countries(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $query = Country::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('currency', 'like', "%{$term}%");
            });
        }

        $countries = $query->limit(50)->get(['id', 'name', 'currency', 'currency_symbol']);

        return response()->json($countries->map(fn ($c) => [
            'id' => $c->id,
            'text' => $c->name . ' (' . $c->currency . ')',
            'currency' => $c->currency,
        ])->values());
    }
}
