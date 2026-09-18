<?php

use App\Http\Controllers\Api\VeraRsvpController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function (): void {
    Route::post('/vera-rsvp', VeraRsvpController::class)->name('vera-rsvp.store');
});
