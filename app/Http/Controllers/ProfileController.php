<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\CountryHelper;

class ProfileController extends Controller
{
    /**
    * Show the profile/preferences form.
    */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'languages' => CountryHelper::getLanguages(),
            'timezones' => $this->getTimezones(),
            'countries' => CountryHelper::getCountries(),
        ]);
    }

    /**
    * Update the profile preferences.
    */
    public function update(Request $request)
    {
        $languages = collect(CountryHelper::getLanguages());
        $localeCodes = $languages->pluck('code')->toArray();
        $localeShort = $languages->pluck('code')->map(fn($c) => substr($c, 0, 2))->unique()->toArray();
        $localeNames = $languages->pluck('name')->toArray();
        $timezones = $this->getTimezones();
        $countries = collect(CountryHelper::getCountries())->pluck('code')->toArray();

        $validated = $request->validate([
            'locale' => ['required', Rule::in(array_merge($localeCodes, $localeShort, $localeNames))],
            'timezone' => ['required', Rule::in($timezones)],
            'country_code' => ['nullable', Rule::in($countries)],
        ]);

        // Normalize locale to short code (e.g., en, id)
        $inputLocale = $validated['locale'];
        $matched = $languages->first(function ($lang) use ($inputLocale) {
            return $lang['code'] === $inputLocale
                || substr($lang['code'], 0, 2) === $inputLocale
                || $lang['name'] === $inputLocale;
        });
        $validated['locale'] = $matched ? substr($matched['code'], 0, 2) : substr($inputLocale, 0, 2);

        $user = $request->user();
        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Preferences updated.');
    }

    protected function getTimezones(): array
    {
        // Curated list of common timezones
        return [
            'UTC',
            'Asia/Jakarta',
            'Asia/Singapore',
            'Asia/Tokyo',
            'Asia/Dubai',
            'Europe/London',
            'Europe/Berlin',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Australia/Sydney',
        ];
    }
}
