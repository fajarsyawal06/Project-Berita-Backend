<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NewsWorkflowController;
use App\Http\Controllers\Api\NewsListingController;
use App\Http\Controllers\Api\MasterData\SatuanKerjaController;
use App\Http\Controllers\Api\MasterData\RoleController;
use App\Http\Controllers\Api\MasterData\JabatanController;
use App\Http\Controllers\Api\MasterData\NewsCategoryController;
use App\Http\Controllers\Api\MasterData\UserController;
use App\Http\Controllers\Api\AnalyticController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\SeasonWinnerController;

use Illuminate\Support\Facades\Artisan;

// Endpoint ini terbuka untuk umum (bisa diakses tanpa token)
Route::post('/login', [AuthController::class, 'login']);
// Rute FR-BR-04 & FR-BR-07
Route::get('/news/public', [NewsListingController::class, 'publicIndex']);
Route::get('/news/trending', [NewsListingController::class, 'trending']); // FR-BR-07 (Harian)
Route::get('/trending', [\App\Http\Controllers\Api\TrendingNewsController::class, 'index']); // FR-TP-01 (Nasional/Lokal 7 Hari)

// Endpoint ini dilindungi (Hanya bisa diakses jika menyertakan Token JWT/Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Endpoint untuk mendapatkan data profil (validasi token frontend)
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/profile/performance', [PerformanceController::class, 'index']);

    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/leaderboard/satuan-kerja', [\App\Http\Controllers\Api\SatuanKerjaLeaderboardController::class, 'index']);
    Route::get('/season-winners', [SeasonWinnerController::class, 'index']);
    Route::get('/points-ledger', [\App\Http\Controllers\Api\PointLedgerController::class, 'index']);
    
    // Endpoint untuk mengupdate preferensi pengguna (seperti dashboard_layout)
    Route::put('/user/preferences', [AuthController::class, 'updatePreferences']);

    // Endpoint untuk mengambil data analitik dinamis
    Route::get('/analytics/data', [AnalyticController::class, 'getAnalyticsData']);

    // Rute umum untuk user login
    Route::post('/news', [NewsController::class, 'store'])->middleware('role:P-01,P-02'); // Kontributor & Editor
    Route::get('/news', [NewsController::class, 'index']); 
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])->middleware('role:P-01,P-02');

    /* =======================================================================
       👇 RUTE BARU KHUSUS FITUR FR-ID-01 (BOOKMARK & DRAFT AUTO-SAVE) 👇
       ======================================================================= */
    
    // PILAR 1: BOOKMARK (Berita Tersimpan)
    Route::post('/news/{id}/bookmark', [NewsController::class, 'toggleBookmark']); // Menyimpan / Batal Menyimpan Berita (Toggle)
    Route::get('/news/bookmarked', [NewsListingController::class, 'bookmarkedIndex']); // Mengambil list berita yang di-bookmark oleh user login

    // PILAR 2: AUTO-SAVE DRAFT (Hanya Kontributor & Editor yang bisa bikin draft)
    Route::post('/news/draft', [NewsController::class, 'storeDraft'])->middleware('role:P-01,P-02'); // Buat draft pertama kali
    Route::put('/news/draft/{id}', [NewsController::class, 'updateDraft'])->middleware('role:P-01,P-02'); // Auto-save update draft (Debounce)

    /* ======================================================================= */

    // Rute khusus workflow (Editor/Admin)
    Route::post('/news/{id}/submit', [NewsWorkflowController::class, 'submit'])->middleware('role:P-01,P-02'); 
    Route::post('/news/{id}/approve', [NewsWorkflowController::class, 'approve'])->middleware('role:P-02,P-04'); 
    Route::post('/news/{id}/reject', [NewsWorkflowController::class, 'reject'])->middleware('role:P-02,P-04');
    $table = Route::get('/news/{id}/audit-trail', [NewsWorkflowController::class, 'auditTrail']);
    
    // Rute untuk melihat draft milik sendiri
    Route::get('/news/my-drafts', [NewsListingController::class, 'myDrafts']);

    // Rute antrean (Khusus Editor, KSK, Admin)
    Route::get('/news/queue', [NewsListingController::class, 'queue'])->middleware('role:P-02,P-03,P-04');
    Route::get('/news/waiting-approval-count', [NewsListingController::class, 'waitingApprovalCount'])->middleware('role:P-02,P-03,P-04');

    /* =======================================================================
       CRUD MASTER DATA (Khusus Administrator P-04)
       ======================================================================= */
    Route::middleware('role:P-04')->group(function () {
        Route::apiResource('/master-data/satuan-kerja', SatuanKerjaController::class);
        Route::apiResource('/master-data/roles', RoleController::class);
        Route::apiResource('/master-data/jabatan', JabatanController::class);
        Route::apiResource('/master-data/kategori-berita', NewsCategoryController::class);
        Route::apiResource('/master-data/pengguna', UserController::class);

   
        
    });
});

// Endpoint publik (diatur proteksinya di dalam Controller)
Route::get('/news/detail/{identifier}', [NewsController::class, 'show']);
Route::get('/news/{id}/export-pdf', [NewsController::class, 'exportPdf']);

// Endpoint engagement (Share & Comments)
Route::post('/news/{id}/share', [NewsController::class, 'share']);
Route::get('/news/{id}/comments', [NewsController::class, 'getComments']);
Route::post('/news/{id}/comments', [NewsController::class, 'postComment'])->middleware('auth:sanctum');

