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
use App\Http\Controllers\Api\MasterData\PermissionController;
use App\Http\Controllers\Api\MasterData\PointConfigurationController;
use App\Http\Controllers\Api\AnalyticController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\SeasonWinnerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AdhocReportController;
use App\Http\Controllers\Api\AdminTutorialVideoController;
use App\Http\Controllers\Api\MasterData\AdminTutorialCategoryController;
use App\Http\Controllers\Api\TutorialVideoUserController;
use App\Http\Controllers\Api\AdminTutorialArticleController;
use App\Http\Controllers\Api\TutorialArticleUserController;

use Illuminate\Support\Facades\Artisan;

// Endpoint ini terbuka untuk umum (bisa diakses tanpa token)
Route::post('/login', [AuthController::class, 'login']);
// Rute FR-BR-04 & FR-BR-07
Route::get('/news/public', [NewsListingController::class, 'publicIndex']);
Route::get('/news/trending', [NewsListingController::class, 'trending']); // FR-BR-07 (Harian)
Route::get('/trending', [\App\Http\Controllers\Api\TrendingNewsController::class, 'index']); // FR-TP-01 (Nasional/Lokal 7 Hari)

// Endpoint Publik untuk Viewer Umum (Guest)
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/leaderboard/satuan-kerja', [\App\Http\Controllers\Api\SatuanKerjaLeaderboardController::class, 'index']);
Route::get('/season-winners', [SeasonWinnerController::class, 'index']);

// Endpoint Master Data Read-Only (Public Dropdown)
Route::get('/satuan-kerja/list', [SatuanKerjaController::class, 'index']);
Route::get('/roles/list', [RoleController::class, 'index']);
Route::get('/kategori-berita/list', [NewsCategoryController::class, 'listActive']);

// Endpoint Daftar Video Panduan (Berdasarkan Role User)
Route::get('/tutorial-videos', [TutorialVideoUserController::class, 'index']);
Route::get('/tutorial-categories', [TutorialVideoUserController::class, 'getCategories']);
Route::get('/tutorial-videos/{id}', [TutorialVideoUserController::class, 'show']);

// Endpoint Daftar Artikel Panduan
Route::get('/tutorial-articles', [TutorialArticleUserController::class, 'index']);
Route::get('/tutorial-articles/{id}', [TutorialArticleUserController::class, 'show']);

// Endpoint ini dilindungi (Hanya bisa diakses jika menyertakan Token JWT/Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Endpoint untuk mendapatkan data profil (validasi token frontend)
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/user/ping', [AuthController::class, 'ping']);

    // Endpoint Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::get('/profile/performance', [PerformanceController::class, 'index']);
    Route::get('/points-ledger', [\App\Http\Controllers\Api\PointLedgerController::class, 'index']);

    // Endpoint untuk mengupdate preferensi pengguna (seperti dashboard_layout)
    Route::put('/user/preferences', [AuthController::class, 'updatePreferences']);

    // Endpoint untuk mengupdate profil (nama, avatar, password)
    Route::post('/user/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);

    // Endpoint untuk share dashboard
    Route::post('/dashboard/share', [\App\Http\Controllers\Api\DashboardShareController::class, 'share']);
    Route::get('/dashboard/shared/{token}', [\App\Http\Controllers\Api\DashboardShareController::class, 'getSharedDashboard']);

    // Endpoint Tutorial Dashboard (FR-PM-01)
    Route::prefix('tutorial-dashboard')->group(function () {
        Route::get('/kpi', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getKpiSummary']);
        Route::get('/top-videos', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getTopVideos']);
        Route::get('/latest', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getLatestVideo']);
        Route::get('/chart-data', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getChartData']);
        Route::get('/comments', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getComments']);
        Route::get('/interaction-logs', [\App\Http\Controllers\Api\TutorialDashboardController::class, 'getInteractionLogs']);
    });

    // Endpoint Daftar Video Panduan (Berdasarkan Role User)
    Route::get('/tutorial-videos', [TutorialVideoUserController::class, 'index']);
    Route::get('/tutorial-categories', [TutorialVideoUserController::class, 'getCategories']);
    Route::get('/tutorial-videos/{id}', [TutorialVideoUserController::class, 'show']);

    // Endpoint Daftar Artikel Panduan
    Route::get('/tutorial-articles', [TutorialArticleUserController::class, 'index']);
    Route::get('/tutorial-articles/{id}', [TutorialArticleUserController::class, 'show']);

    // Endpoint untuk mengambil data analitik dinamis
    Route::get('/analytics/data', [AnalyticController::class, 'getAnalyticsData']);

    Route::get('/reports/templates', [ReportController::class, 'getTemplates'])->middleware('permission:reports.view');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->middleware('permission:reports.generate');

    // Endpoint Laporan Ad-hoc
    Route::get('/reports/adhoc/columns', [AdhocReportController::class, 'getColumns'])->middleware('permission:reports.adhoc');
    Route::post('/reports/adhoc/preview', [AdhocReportController::class, 'preview'])->middleware('permission:reports.adhoc');
    Route::post('/reports/adhoc/export', [AdhocReportController::class, 'export'])->middleware('permission:reports.adhoc');



    // Rute umum untuk user login
    Route::post('/news', [NewsController::class, 'store'])->middleware('permission:news.create'); // Kontributor & Editor
    Route::get('/news', [NewsController::class, 'index']);
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])->middleware('permission:news.create');

    /* =======================================================================
       👇 RUTE BARU KHUSUS FITUR FR-ID-01 (BOOKMARK & DRAFT AUTO-SAVE) 👇
       ======================================================================= */

    // PILAR 1: BOOKMARK (Berita Tersimpan)
    Route::post('/news/{id}/bookmark', [NewsController::class, 'toggleBookmark'])->middleware('permission:news.bookmark'); // Menyimpan / Batal Menyimpan Berita (Toggle)
    Route::get('/news/bookmarked', [NewsListingController::class, 'bookmarkedIndex'])->middleware('permission:news.bookmark'); // Mengambil list berita yang di-bookmark oleh user login

    // PILAR 2: AUTO-SAVE DRAFT (Hanya Kontributor & Editor yang bisa bikin draft)
    Route::post('/news/draft', [NewsController::class, 'storeDraft'])->middleware('permission:news.create'); // Buat draft pertama kali
    Route::put('/news/draft/{id}', [NewsController::class, 'updateDraft'])->middleware('permission:news.create'); // Auto-save update draft (Debounce)

    /* ======================================================================= */

    // Rute khusus workflow (Editor/Admin)
    Route::post('/news/{id}/submit', [NewsWorkflowController::class, 'submit'])->middleware('permission:news.create');
    Route::post('/news/{id}/approve', [NewsWorkflowController::class, 'approve'])->middleware('permission:news.verify');
    Route::post('/news/{id}/reject', [NewsWorkflowController::class, 'reject'])->middleware('permission:news.verify');
    Route::post('/news/{id}/force-publish', [NewsWorkflowController::class, 'forcePublish'])->middleware('permission:news.force_publish');
    $table = Route::get('/news/{id}/audit-trail', [NewsWorkflowController::class, 'auditTrail']);

    // Rute untuk melihat draft milik sendiri
    Route::get('/news/my-drafts', [NewsListingController::class, 'myDrafts']);

    // Rute antrean (Khusus Editor, KSK, Admin)
    Route::get('/news/queue', [NewsListingController::class, 'queue'])->middleware('permission:news.queue.view');
    Route::get('/news/waiting-approval-count', [NewsListingController::class, 'waitingApprovalCount'])->middleware('permission:news.queue.view');

    /* =======================================================================
       CRUD MASTER DATA (Khusus Administrator atau yang memiliki master_data.manage)
       ======================================================================= */
    Route::middleware('permission:master_data.manage')->group(function () {
        Route::apiResource('/master-data/satuan-kerja', SatuanKerjaController::class);
        Route::get('/master-data/permissions', [PermissionController::class, 'index']);
        Route::apiResource('/master-data/roles', RoleController::class);
        Route::apiResource('/master-data/jabatan', JabatanController::class);
        Route::apiResource('/master-data/kategori-berita', NewsCategoryController::class);
        Route::apiResource('/master-data/pengguna', UserController::class);
        
        // Konfigurasi Poin (Hanya index, show, update)
        Route::get('/master-data/konfigurasi-poin', [PointConfigurationController::class, 'index']);
        Route::get('/master-data/konfigurasi-poin/{id}', [PointConfigurationController::class, 'show']);
        Route::put('/master-data/konfigurasi-poin/{id}', [PointConfigurationController::class, 'update']);

        // CRUD Video Panduan (Khusus Administrator)
        Route::get('/admin/tutorial-categories', [AdminTutorialVideoController::class, 'getCategories']);
        Route::apiResource('/admin/tutorial-videos', AdminTutorialVideoController::class);
        Route::apiResource('/master-data/kategori-video-panduan', AdminTutorialCategoryController::class);

        // CRUD Artikel Panduan (Khusus Administrator)
        Route::apiResource('/admin/tutorial-articles', AdminTutorialArticleController::class);
    });
});

// Endpoint publik (diatur proteksinya di dalam Controller)
Route::get('/news/detail/{identifier}', [NewsController::class, 'show']);
Route::get('/news/{id}/export-pdf', [NewsController::class, 'exportPdf']);

// Endpoint engagement (Share & Comments)
Route::post('/news/{id}/share', [NewsController::class, 'share']);
Route::get('/news/{id}/comments', [NewsController::class, 'getComments']);
Route::post('/news/{id}/comments', [NewsController::class, 'postComment'])->middleware('auth:sanctum');
