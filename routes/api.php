<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NewsWorkflowController;
use App\Http\Controllers\Api\NewsListingController;
use Illuminate\Support\Facades\Route;

// Endpoint ini terbuka untuk umum (bisa diakses tanpa token)
Route::post('/login', [AuthController::class, 'login']);
// Rute FR-BR-04
Route::get('/news/public', [NewsListingController::class, 'publicIndex']);

// Endpoint ini dilindungi (Hanya bisa diakses jika menyertakan Token JWT/Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Endpoint untuk mendapatkan data profil (validasi token frontend)
    Route::get('/me', [AuthController::class, 'me']);

    // Rute umum untuk user login
    Route::post('/news', [NewsController::class, 'store'])->middleware('role:P-01,P-02'); // Kontributor & Editor
    Route::get('/news', [NewsController::class, 'index']); // Akan kita handle difilter via Controller (milik sendiri vs antrean)
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])->middleware('role:P-01,P-02');

    // ... (Route yang sudah ada) ...

    // Rute khusus workflow (Editor/Admin)
    Route::post('/news/{id}/submit', [NewsWorkflowController::class, 'submit'])->middleware('role:P-01,P-02'); // Submit dari DRAFT
    Route::post('/news/{id}/approve', [NewsWorkflowController::class, 'approve'])->middleware('role:P-02,P-04'); // Editor & Admin
    Route::post('/news/{id}/reject', [NewsWorkflowController::class, 'reject'])->middleware('role:P-02,P-04');
    Route::get('/news/{id}/audit-trail', [NewsWorkflowController::class, 'auditTrail']);
    
    // Rute untuk melihat draft milik sendiri
    Route::get('/news/my-drafts', [NewsListingController::class, 'myDrafts']);

    // Rute antrean (Khusus Editor, KSK, Admin)
    Route::get('/news/queue', [NewsListingController::class, 'queue'])->middleware('role:P-02,P-03,P-04');
});

// Endpoint publik (diatur proteksinya di dalam Controller)
Route::get('/news/detail/{identifier}', [NewsController::class, 'show']);