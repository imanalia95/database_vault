<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Donor routes - Import routes must come before resource route
Route::post('donors/bulk-delete', [DonorController::class, 'bulkDelete'])->name('donors.bulk-delete');
Route::get('donors/import', [DonorController::class, 'importForm'])->name('donors.import.form');
Route::post('donors/import', [DonorController::class, 'import'])->name('donors.import');
Route::post('donors/{donor}/assign-labels', [DonorController::class, 'assignLabels'])->name('donors.assign-labels');
Route::post('donors/{donor}/remove-from-client', [DonorController::class, 'removeFromClient'])->name('donors.remove-from-client');
Route::delete('donors/bulk-delete-ajax', [DonorController::class, 'bulkDeleteAjax'])->name('donors.bulk-delete-ajax');
Route::get('donors/export', [DonorController::class, 'export'])->name('donors.export');
Route::get('donors/download', [DonorController::class, 'download'])->name('donors.download');
Route::post('donors/download/export', [DonorController::class, 'downloadExport'])->name('donors.download.export');
Route::get('donors/bulk-delete-form', [DonorController::class, 'bulkDeleteForm'])->name('donors.bulkDeleteForm');
Route::post('donors/bulk-delete-phones', [DonorController::class, 'bulkDeletePhones'])->name('donors.bulkDeletePhones');
Route::resource('donors', DonorController::class);

// Client routes
Route::resource('clients', ClientController::class);

// Label routes
Route::resource('labels', LabelController::class);

Route::get('blasting/download', [DonorController::class, 'blastingDownloadForm'])->name('blasting.download.form');
Route::post('blasting/download', [DonorController::class, 'blastingDownloadExport'])->name('blasting.download.export');

Route::post('test-bulk-delete', function () {
    return 'Bulk delete test route hit!';
});
