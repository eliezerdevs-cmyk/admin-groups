<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Filament\Http\Middleware\Authenticate;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/private-file', function (\Illuminate\Http\Request $request) {
    $path = $request->query('path');
    if (! $path || ! Storage::disk('private')->exists($path)) {
        abort(404);
    }
    return Storage::disk('private')->response($path);
})->middleware(Authenticate::class)->name('private.file');
