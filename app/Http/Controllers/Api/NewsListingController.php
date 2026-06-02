<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Facades\Auth;

class NewsListingController extends Controller
{
    /**
     * Menangani seluruh filter dinamis dari Frontend (React)
     */
    private function applyFilters($query, Request $request)
    {
        // 1. Filter Kategori Berita
        $query->when($request->category_id, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        });

        // 2. Filter Satuan Kerja (Unit Kerja)
        $query->when($request->satuan_kerja_id, function ($q, $satuanKerjaId) {
            $q->where('satuan_kerja_id', $satuanKerjaId);
        });

        // 3. Filter Jenis Berita (cth: 'TEKS', 'VIDEO', 'FOTO')
        $query->when($request->jenis_berita, function ($q, $jenisBerita) {
            $q->where('jenis_berita', $jenisBerita);
        });

        // 4. Filter Jenis Publikasi (cth: 'INTERNAL', 'UMUM')
        $query->when($request->jenis_publikasi, function ($q, $jenisPublikasi) {
            $q->where('jenis_publikasi', $jenisPublikasi);
        });

        // 5. Filter Pencarian Text (Global Search)
        $query->when($request->search, function ($q, $search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->where('judul', 'like', "%{$search}%")
                     ->orWhere('what_content', 'like', "%{$search}%")
                     ->orWhere('who_involved', 'like', "%{$search}%");
            });
        });

        return $query;
    }

    /**
     * Menangani pengurutan data secara aman (Mencegah SQL Injection)
     */
    private function applySorting($query, Request $request)
    {
        // Ambil input sorting, berikan nilai bawaan jika tidak diisi oleh frontend
        $sortBy = $request->input('sort_by', 'created_at'); // Parameter kolom
        $sortOrder = $request->input('sort_order', 'desc'); // Parameter arah (asc/desc)

        // Whitelist kolom dan arah sorting yang diizinkan demi keamanan database
        $allowedSortBy = ['created_at', 'views_count', 'judul'];
        $allowedSortOrder = ['asc', 'desc'];

        // Jika parameter valid, terapkan urutannya ke query
        if (in_array($sortBy, $allowedSortBy) && in_array($sortOrder, $allowedSortOrder)) {
            // Jika memilih penonton, urutkan berdasarkan 'views_count'
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Fallback default jika parameter yang dikirim aneh/tidak valid
            $query->latest(); 
        }

        return $query;
    }

/**
     * FR-BR-04: Daftar Publikasi Berita (Non-Draft) untuk Viewer Umum
     */
    public function publicIndex(Request $request)
    {
        $query = News::with([
            'category:id,nama_kategori,warna_badge,ikon',
            'user:id,nama_lengkap',
            'satuanKerja:id,nama_satuan_kerja', 
            'attachments' => function($q) {
                // Ambil gambar saja untuk kebutuhan thumbnail Card UI
                $q->where('file_type', 'image')->select('id', 'news_id', 'file_path');
            }
        ])
        // Logika utama FR-BR-04: Tampilkan semua kecuali Draft
        ->where('status', '!=', 'DRAFT');

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        return response()->json($query->paginate(10));
    }

    /**
     * 2. ENDPOINT DRAFT SAYA (Kontributor)
     */
    public function myDrafts(Request $request)
    {
        $query = News::with(['category', 'attachments'])
                     ->where('user_id', Auth::id())
                     ->whereIn('status', ['DRAFT', 'REJECTED']);

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        return response()->json($query->paginate(10));
    }

    /**
     * 3. ENDPOINT ANTREAN EDITOR (Verifikasi)
     */
    public function queue(Request $request)
    {
        $query = News::with(['category', 'user:id,nama_lengkap', 'attachments'])
                     ->where('status', 'SENT_WAITING_VERIFICATION');

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        return response()->json($query->paginate(10));
    }

    /**
     * FR-BR-07: Mengambil top 5 berita trending harian
     */
    public function trending(Request $request)
    {
        $query = News::with([
            'category:id,nama_kategori,warna_badge,ikon',
            'user:id,nama_lengkap',
            'satuanKerja:id,nama_satuan_kerja', 
            'attachments' => function($q) {
                // Ambil gambar saja untuk kebutuhan thumbnail
                $q->where('file_type', 'image')->select('id', 'news_id', 'file_path');
            }
        ])
        ->where('status', 'PUBLISHED')
        ->where('created_at', '>=', now()->subHours(24))
        ->orderByRaw('(views_count * 1) + (shares_count * 2) + (comments_count * 3) DESC')
        ->limit(5)
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil berita trending',
            'data' => $query
        ]);
    }

    /**
     * FR-BR-07: Menghitung jumlah antrean persetujuan (Real-time polling)
     */
    public function waitingApprovalCount(Request $request)
    {
        $count = News::where('status', 'SENT_WAITING_VERIFICATION')->count();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil jumlah antrean persetujuan',
            'data' => [
                'count' => $count
            ]
        ]);
    }
}