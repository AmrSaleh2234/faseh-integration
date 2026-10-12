<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Staff;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\RolePolicy;
use App\Policies\StaffPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        foreach (config('local_permissions') as $permission) {
            Gate::define($permission, fn ($user) => $user->permits($permission));
        }
    }
}
