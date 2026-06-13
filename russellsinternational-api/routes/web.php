<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return response()->json([
        'name' => "Russell's International API",
        'version' => '1.0.0',
        'status' => 'running',
        'admin' => url('/admin'),
        'api' => url('/api/v1'),
    ]);
});

Route::get('/storage/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);

    $file = storage_path('app/public/'.$path);
    abort_unless(is_file($file), 404);

    return Response::file($file, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*');

Route::get('/admin/lock', function (Request $request) {
    $intended = $request->query('intended');

    if (is_string($intended) && Str::startsWith($intended, url('/admin'))) {
        $request->session()->put('url.intended', $intended);
    }

    $request->session()->forget('admin_last_activity_at');

    if (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
    }

    return redirect()
        ->route('filament.admin.auth.login')
        ->with('status', 'Your admin session was locked after 5 minutes of inactivity. Please login again.');
})->name('admin.lock');
