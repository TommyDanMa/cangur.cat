<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        Gate::define('teacher', function (User $user) {
            return $user->role === 'teacher';
        });

        Validator::extend('allowed_email', function ($attribute, $value) {
            $value = strtolower(trim($value));

            $domain = substr(strchr($value, '@') ?: '', 1);
            if ($domain === '') {
                return false;
            }

            $allowedDomains = array_map('strtolower', (array) config('allowed_emails.allowed_domains', []));

            return in_array($domain, $allowedDomains, true);
        }, __('Aquest correu no està permès per al registre.'));
    }
}
