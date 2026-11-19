<?php

use App\Http\Controllers\LegacyController;
use Illuminate\Support\Facades\Route;

// The original application keeps its URLs. This route is intentionally
// registered without the "web" middleware so the legacy sessions and forms
// behave exactly as before.
Route::any('{path?}', [LegacyController::class, 'handle'])
    ->where('path', '.*');
