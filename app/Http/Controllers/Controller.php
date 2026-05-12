<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function dashboardRedirect(): string
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return route('admin.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
