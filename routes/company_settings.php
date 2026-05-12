<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CompanySettingsController;

Route::middleware(['web', 'auth:admin'])->prefix('admin')->group(function () {
    Route::get('company-settings', [CompanySettingsController::class, 'edit'])->name('admin.company-settings.edit');
    Route::put('company-settings', [CompanySettingsController::class, 'update'])->name('admin.company-settings.update');
    // Notification emails CRUD
    Route::get('notification-emails', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'index'])->name('admin.notification-emails.index');
    Route::get('notification-emails/create', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'create'])->name('admin.notification-emails.create');
    Route::post('notification-emails', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'store'])->name('admin.notification-emails.store');
    Route::get('notification-emails/{id}/edit', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'edit'])->name('admin.notification-emails.edit');
    Route::put('notification-emails/{id}', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'update'])->name('admin.notification-emails.update');
    Route::delete('notification-emails/{id}', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'destroy'])->name('admin.notification-emails.destroy');

    Route::post('notification-emails/{id}/toggle-status', [\App\Http\Controllers\Admin\NotificationEmailController::class, 'toggleStatus'])->name('admin.notification-emails.toggleStatus');

    
});
