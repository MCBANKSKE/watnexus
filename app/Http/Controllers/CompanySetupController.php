<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanySetupController extends Controller
{
    public function showCompanySetupForm()
    {
        $user = Auth::user();

        if ($user->companies()->exists()) {
            return redirect()->route('filament.admin.auth.login')->with('info', 'You already have a company configured.');
        }

        $countries = Country::orderBy('name')->get();

        return view('company-setup', compact('countries', 'user'));
    }

    public function storeCompanySetup(Request $request)
    {
        $user = Auth::user();

        if ($user->companies()->exists()) {
            return redirect()->route('filament.admin.auth.login')->with('info', 'You already have a company configured.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'currency_id' => ['nullable', 'exists:countries,id'],
        ]);

        try {
            DB::transaction(function () use ($validated, $user) {
                $company = Company::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'website' => $validated['website'],
                    'registration_number' => $validated['registration_number'],
                    'tax_number' => $validated['tax_number'],
                    'logo' => $validated['logo'],
                    'address' => $validated['address'],
                    'country_id' => $validated['country_id'],
                    'city_id' => $validated['city_id'],
                    'currency_id' => $validated['currency_id'],
                ]);

                $user->attachToCompany($company, 'admin');
            });

            if (! $user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();

                return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
            }

            return redirect()->route('filament.admin.pages.dashboard')->with('success', 'Company setup completed successfully! Welcome to your dashboard.');

        } catch (\Exception $e) {
            $debug = config('app.debug')
                ? ' Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine()
                : '';

            return back()->withInput()->with('error', 'There was an error setting up your company. Please try again.'.$debug);
        }
    }
}
