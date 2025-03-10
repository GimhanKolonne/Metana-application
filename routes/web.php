<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\DashboardController;
use App\Models\JobApplication;
use App\Mail\ApplicationFollowUp;
use App\Http\Controllers\WebhookTestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/test-webhook', [WebhookTestController::class, 'testWebhook']);



Route::get('/', [JobApplicationController::class, 'showForm'])->name('job-application.form');
Route::post('/apply', [JobApplicationController::class, 'store'])->name('job-application.store');
Route::get('/success', [JobApplicationController::class, 'success'])->name('job-application.success');