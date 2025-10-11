<?php

use Illuminate\Support\Facades\Route;
use Efati\ModuleGenerator\Http\Controllers\ModuleGeneratorController;

Route::group(['middleware' => 'web'], function () {
    Route::get('/module-generator', [ModuleGeneratorController::class, 'index'])->name('module-generator.index');
    Route::post('/module-generator/generate', [ModuleGeneratorController::class, 'generate'])->name('module-generator.generate');
});