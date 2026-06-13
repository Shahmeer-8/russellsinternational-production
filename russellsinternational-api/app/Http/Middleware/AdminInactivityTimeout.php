<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminInactivityTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check() || $request->is('admin/login') || $request->is('admin/lock')) {
            return $next($request);
        }

        $timeoutSeconds = max(1, (int) config('admin.idle_timeout_minutes', 5)) * 60;
        $lastActivityAt = (int) $request->session()->get('admin_last_activity_at', time());

        if ((time() - $lastActivityAt) >= $timeoutSeconds) {
            $request->session()->put('url.intended', $request->fullUrl());
            $request->session()->forget('admin_last_activity_at');

            Auth::guard('web')->logout();

            return redirect()
                ->route('filament.admin.auth.login')
                ->with('status', 'Your admin session was locked after 5 minutes of inactivity. Please login again.');
        }

        $request->session()->put('admin_last_activity_at', time());

        return $next($request);
    }
}
