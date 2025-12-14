<?php

use App\Http\Controllers\FacebookController;
use App\Models\FacebookPage;
use App\Models\FacebookUser;
use App\Models\FacebookPostLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();
    return Inertia::render('Dashboard', [
        'facebookConnected' => $user->facebookUsers()->exists(),
        'facebookUsers' => FacebookUser::where('user_id', $user->id)
            ->select('id', 'facebook_id', 'name')
            ->get(),
        'facebookPages' => FacebookPage::where('user_id', $user->id)
            ->select('id', 'page_id', 'page_name', 'facebook_user_id')
            ->with('facebookUser:id,name')
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/posts', function (Request $request) {
    $user = auth()->user();

    $baseQuery = FacebookPostLog::where('user_id', $user->id)
        ->with([
            'facebookPage:id,page_name,page_id',
            'facebookUser:id,name',
        ])
        ->latest();

    $statusFilter = $request->query('status');
    $statuses = [
        'queuedLogs' => ['queued', 'processing'],
        'scheduledLogs' => ['scheduled'],
        'completedLogs' => ['completed'],
        'failedLogs' => ['failed'],
    ];

    $logs = [];

    foreach ($statuses as $key => $status) {
        $query = (clone $baseQuery);

        if ($statusFilter) {
            $query->where('status', $statusFilter);

            if (!in_array($statusFilter, $status, true)) {
                $logs[$key] = collect();
                continue;
            }
        } else {
            $query->whereIn('status', $status);
        }

        $logs[$key] = $query->take(50)->get();
    }

    return Inertia::render('PostLogs', [
        ...$logs,
        'statusFilter' => $statusFilter,
    ]);
})->middleware(['auth', 'verified'])->name('posts.index');

Route::middleware('auth')->group(function () {
    Route::get('/auth/facebook', [FacebookController::class, 'redirect']);
    Route::get('/auth/facebook/callback', [FacebookController::class, 'callback']);
    Route::post('/facebook/get-pages', [FacebookController::class, 'getPages']);
    Route::post('/facebook/post', [FacebookController::class, 'store']);
    Route::post('/facebook/bulk-post', [FacebookController::class, 'bulkPost']);
});

require __DIR__ . '/settings.php';
