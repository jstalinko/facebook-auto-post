<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\FacebookController;
use App\Models\FacebookPage;

Route::get('/', function () {

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();
    return Inertia::render('Dashboard', [
        'facebookConnected' => (bool) $user->facebook_token,
        'facebookPages' => FacebookPage::where('user_id', $user->id)
            ->select('id', 'page_id', 'page_name')
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/auth/facebook', [FacebookController::class, 'redirect']);
    Route::get('/auth/facebook/callback', [FacebookController::class, 'callback']);
    Route::get('/auth/facebook/pages', [FacebookController::class, 'redirectPages']);
    Route::post('/facebook/get-pages', [FacebookController::class, 'getPages']);
    Route::post('/facebook/post', [FacebookController::class, 'store']);
});

require __DIR__ . '/settings.php';
