<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $user = Auth::user();
        $user->load('role');

        $query = News::query();

        // Jika status yang direquest adalah antrean verifikasi dan rolenya memiliki akses verifikasi, maka jangan filter user_id
        // Role kode: P-02 (Verifikator), P-03 (KSK), P-04 (Admin)
        $isVerifikator = in_array($user->role->kode_role ?? '', ['P-02', 'P-03', 'P-04']);

        if ($status === 'SENT_WAITING_VERIFICATION' && $isVerifikator) {
            // Tampilkan semua berita yang menunggu verifikasi (tanpa memfilter user_id penulis)
        } else {
            // Selain itu, hanya tampilkan milik sendiri
            $query->where('user_id', $user->id);
        }
        
        if ($status) {
            $query->where('status', strtoupper($status));
        }

        $news = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $news
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Tentukan aturan validasi dasar
        $action = $request->input('action', 'save_draft'); // Default ke draft jika tidak ada
        $isSubmit = $action === 'submit';

        // Validasi dinamis: Jika 'submit', maka wajib. Jika 'draft', maka opsional (nullable)
        $rules = [
            'judul'            => 'required|string|max:255',
            'category_id'      => 'required|exists:news_categories,id',
            'what_content'     => $isSubmit ? 'required|string' : 'nullable|string',
            'who_involved'     => $isSubmit ? 'required|string' : 'nullable|string',
            'when_occurred'    => $isSubmit ? 'required|string' : 'nullable|string',
            'where_location'   => $isSubmit ? 'required|string' : 'nullable|string',
            'why_happened'     => $isSubmit ? 'required|string' : 'nullable|string',
            'how_resolved'     => $isSubmit ? 'required|string' : 'nullable|string',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'location_address' => 'nullable|string',
            
            // Validasi file lampiran (maksimal 10MB per file)
            'attachments'   => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,mp4,pdf,docx,xlsx,mp3,wav|max:10240'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Mulai Transaksi Database
        DB::beginTransaction();

        try {
            // Dapatkan user yang sedang login dari token Sanctum
            $user = Auth::user();

            // 3. Simpan Teks Berita ke Tabel `news`
            $news = News::create([
                'user_id'          => $user->id,
                'category_id'      => $request->category_id,
                'satuan_kerja_id'  => $user->satuan_kerja_id, // Mengambil otomatis dari profil user
                'judul'            => $request->judul,
                'slug'             => Str::slug($request->judul),
                'what_content'     => $request->what_content,
                'who_involved'     => $request->who_involved,
                'when_occurred'    => $request->when_occurred,
                'where_location'   => $request->where_location,
                'why_happened'     => $request->why_happened,
                'how_resolved'     => $request->how_resolved,
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'location_address' => $request->location_address,
                'status'           => $isSubmit ? 'SENT_WAITING_VERIFICATION' : 'DRAFT',
            ]);

            // 4. Proses File Lampiran (Jika Ada)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    
                    // Simpan file ke folder storage/app/public/news_attachments
                    // Pastikan Anda sudah menjalankan `php artisan storage:link`
                    $path = $file->store('news_attachments', 'public');

                    // Deteksi tipe file untuk kolom 'file_type'
                    $mime = $file->getClientMimeType();
                    $fileType = 'document';
                    if (str_starts_with($mime, 'image/')) $fileType = 'image';
                    elseif (str_starts_with($mime, 'video/')) $fileType = 'video';
                    elseif (str_starts_with($mime, 'audio/')) $fileType = 'voice_note';

                    // Simpan data file ke tabel `news_attachments`
                    NewsAttachment::create([
                        'news_id'           => $news->id,
                        'file_type'         => $fileType,
                        'file_path'         => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type'         => $mime,
                        'file_size_bytes'   => $file->getSize(),
                    ]);
                }
            }

            // 5. Commit Transaksi (Simpan Permanen)
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $isSubmit ? 'Berita berhasil dikirim untuk verifikasi' : 'Draft berita berhasil disimpan',
                'data' => $news->load('attachments') // Eager load lampiran untuk respon
            ], 201);

        } catch (\Exception $e) {
            // Jika ada error (misal disk penuh), batalkan semua query database
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan berita.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $identifier)
    {
        \Log::info('Accessing news detail:', ['identifier' => $identifier]);
       // 1. Tarik berita berdasarkan ID, Slug, atau Kode Berita (Fleksibel untuk Frontend)
        $news = News::with([
            'user', // Profil pembuat lengkap
            'satuanKerja',
            'category',
            'attachments', // Termasuk gambar dan dokumen pendukung
            'statusLogs' => function($q) {
                // Tarik histori perubahan status dan siapa yang mengubahnya
                $q->with('actor:id,nama_lengkap')->latest();
            }
        ])
        ->where('id', $identifier)
        ->orWhere('kode_berita', $identifier)
        ->orWhere('slug', $identifier)
        ->first();

        if (!$news) {
            \Log::warning('News not found', ['identifier' => $identifier]);
            return response()->json(['message' => 'Berita tidak ditemukan'], 404);
        }

        // 2. Proteksi Keamanan Akses (RBAC Sederhana)
        // Jika statusnya BUKAN dipublikasikan, maka cek apakah user memiliki akses
        if ($news->status !== 'PUBLISHED') {
            // Gunakan guard 'sanctum' untuk mengecek token API
            if (!Auth::guard('sanctum')->check()) {
                \Log::warning('Auth check failed. Token missing or invalid.');
                return response()->json(['message' => 'Akses ditolak. Anda harus login untuk melihat draf/antrean ini.'], 401);
            }

            $user = Auth::guard('sanctum')->user();
            $user->load('role');

            \Log::info('User accessed detail:', [
                'user_id' => $user->id,
                'role' => $user->role->kode_role ?? 'null',
                'news_user_id' => $news->user_id
            ]);

            // Jika user BUKAN pembuat berita ini DAN BUKAN editor/admin, tolak aksesnya!
            // (Sesuaikan logika pengecekan role 'is_editor' ini dengan desain role database Anda)
            $isMaker = ($news->user_id === $user->id);
            $isEditor = in_array($user->role->kode_role ?? '', ['P-02', 'P-03', 'P-04']); // P-02: Verifikator, P-03: KSK, P-04: Admin
            
            if (!$isMaker && !$isEditor) {
                \Log::warning('User does not have permission', ['isMaker' => $isMaker, 'isEditor' => $isEditor]);
                return response()->json(['message' => 'Anda tidak memiliki izin untuk melihat dokumen ini.'], 403);
            }
        } else {
            // 3. Increment Counter Penonton
            // Jika berita publik (PUBLISHED) diakses, naikkan jumlah penontonnya
            $news->increment('views_count');
        }

        // 4. Kirim respons Metadata Lengkap ke React
        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
            
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $news = News::where('user_id', Auth::id())->findOrFail($id);
        $news->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Berita berhasil dihapus'
        ], 200);
    }
}
