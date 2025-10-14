<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjetoInvitation;

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
               
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $pendingInvitationsCount = ProjetoInvitation::where('email', Auth::user()->email)
                                                            ->where('status', 'pendente')
                                                            ->count();
                $view->with('pendingInvitationsCount', $pendingInvitationsCount);
            } else {
                $view->with('pendingInvitationsCount', 0);
            }
        });
    }
}
