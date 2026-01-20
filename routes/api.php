<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\InsightController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\BrandingController;
use App\Http\Controllers\Api\AssetController;

Route::middleware(['auth:sanctum', 'web'])->group(function () {
    Route::apiResource('templates', TemplateController::class)->names([
        'index' => 'api.templates.index',
        'store' => 'api.templates.store',
        'show' => 'api.templates.show',
        'update' => 'api.templates.update',
        'destroy' => 'api.templates.destroy',
    ]);
    Route::post('templates/{template}/duplicate', [TemplateController::class, 'duplicate'])->name('api.templates.duplicate');
    Route::post('templates/{template}/apply-branding', [TemplateController::class, 'applyBranding'])->name('api.templates.apply-branding');

    Route::get('brandings', [BrandingController::class, 'index'])->name('api.brandings.index');
    Route::get('user-branding', [BrandingController::class, 'getUserBranding'])->name('api.user-branding');

    Route::get('assets/{asset}', [AssetController::class, 'show'])->name('api.assets.show');
    Route::post('assets/{asset}/update-visual', [AssetController::class, 'updateVisual'])->name('api.assets.update-visual');

    // Template Categories (for quick add in image editor)
    Route::get('template-categories', [\App\Http\Controllers\Api\TemplateCategoryController::class, 'index'])->name('api.template-categories.index');
    Route::post('template-categories', [\App\Http\Controllers\Api\TemplateCategoryController::class, 'store'])->name('api.template-categories.store');
});