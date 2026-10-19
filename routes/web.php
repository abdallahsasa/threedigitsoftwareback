<?php

use App\Http\Controllers\AdminPdfController;
use App\Http\Controllers\CustomerPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn () => redirect('/admin/login'))->name('login');

// 1. Secure Customer Portal (Public with high-entropy token)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/docs/{token}', [CustomerPortalController::class, 'viewDocument'])->name('document.view');
    Route::get('/docs/{token}/download', [CustomerPortalController::class, 'downloadPdf'])->name('document.download');
});

// 2. Admin Document PDF Views (Authenticated via Filament session/auth)
Route::middleware(['web', 'auth'])->prefix('admin/pdf')->name('admin.pdf.')->group(function () {
    Route::get('/invoices/{id}', [AdminPdfController::class, 'invoice'])->name('invoice');
    Route::get('/quotations/{id}', [AdminPdfController::class, 'quotation'])->name('quotation');
    Route::get('/receipts/{id}', [AdminPdfController::class, 'receipt'])->name('receipt');
    Route::get('/statements/{clientId}', [AdminPdfController::class, 'statement'])->name('statement');
});
