<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('allowed_email', function ($attribute, $value) {
            $value = strtolower(trim($value));

            $whitelistEmails = array_map('strtolower', (array) config('allowed_emails.whitelist_emails', []));
            if (in_array($value, $whitelistEmails, true)) {
                return true;
            }

            $domain = substr(strchr($value, '@') ?: '', 1);
            if ($domain === '') {
                return false;
            }

            $alloedDomains = array_filter([
                strtolower((string) config('allowed_emails.org_domain')),
                ...array_map('strtolower', (array) config('allowed_emails.extra_domains', [])),
            ]);

            return in_array($domain, $alloedDomains, true);
        }, __('Aquest correu no està permès per al registre.'));
    }
}
