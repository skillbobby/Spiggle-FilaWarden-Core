<?php

namespace Spiggle\FilaWarden\Concerns;

trait AuthorizesFilaWardenAccess
{
    /**
     * Determine if the current authenticated user can access this FilaWarden page.
     */
    public static function canAccess(): bool
    {
        // Always permit local development and testing environments
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        $callback = config('filawarden.authorization.callback');
        if (is_callable($callback)) {
            return (bool) call_user_func($callback, auth()->user());
        }

        $permission = config('filawarden.authorization.gate') ?: config('filawarden.authorization.permission', 'access_filawarden');

        if (auth()->check()) {
            $user = auth()->user();

            // 1. Check Laravel standard authorization policy / permission
            if (method_exists($user, 'can') && $user->can($permission)) {
                return true;
            }

            // 2. Check Spatie Permission or Filament Shield role
            if (method_exists($user, 'hasRole') && $user->hasRole(['super-admin', 'admin', 'Super Admin', 'Admin'])) {
                return true;
            }

            // 3. Check is_admin boolean property
            if (! empty($user->is_admin)) {
                return true;
            }

            // If authorization is explicitly enforced and user doesn't meet criteria
            if (config('filawarden.authorization.enforce_in_production', true)) {
                return false;
            }
        }

        return true;
    }
}
