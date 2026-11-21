<?php

declare(strict_types=1);

use App\Http\Controllers\DeployController;
use Illuminate\Support\Facades\Route;

Route::get('/deploy/{token}', DeployController::class)
    ->middleware('throttle:5,1')
    ->name('deploy.run');
