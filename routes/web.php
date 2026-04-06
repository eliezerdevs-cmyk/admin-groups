<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Filament\Http\Middleware\Authenticate;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/private-file/{path}', function (string $path) {
    if (! Storage::disk('private')->exists($path)) {
        abort(404);
    }
    return Storage::disk('private')->response($path);
})->where('path', '.*')->middleware(Authenticate::class)->name('private.file');
