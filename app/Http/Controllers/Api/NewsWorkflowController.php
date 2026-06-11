<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsStatusLog;
use App\Models\User;
use App\Notifications\NewsSubmittedNotification;
use App\Notifications\NewsStatusUpdatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\PointService;

class NewsWorkflowController extends Controller
{
    // 1. Fungsi untuk Kontributor mengirim Draft ke Editor
    public function submit(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $user = Auth::user();

        // VALIDASI FSM: Hanya berita DRAFT yang bisa di-submit
        if ($news->status !== 'DRAFT') {
            return response()->json(['message' => 'Hanya berita Draft yang bisa dikirim untuk verifikasi.'], 400);
        }

        // Opsional: Validasi RBAC tambahan (pastikan hanya pemilik yang bisa kirim)
        if ($news->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak untuk mengirim berita ini.'], 403);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $news->status;
            $news->status = 'SENT_WAITING_VERIFICATION';
            $news->save();

            // Catat ke Audit Trail
            NewsStatusLog::create([
                'news_id'    => $news->id,
                'user_id'    => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $news->status,
                'reason'     => 'Dikirim untuk proses verifikasi oleh penulis.'
            ]);

            // Notify Editors/Admins
            $verifiers = User::whereHas('role', function($q) {
                $q->whereIn('kode_role', ['P-02', 'P-04']);
            })->get();
            foreach ($verifiers as $verifier) {
                $verifier->notify(new NewsSubmittedNotification($news));
            }

            DB::commit();
            return response()->json(['message' => 'Berita berhasil dikirim ke antrean Editor.', 'status' => $news->status]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    // 2. Fungsi untuk Editor MENYETUJUI berita
    public function approve(Request $request, $id)
    {
        $news = News::findOrFail($id);
        
        // VALIDASI FSM: Hanya berita di antrean yang bisa disetujui
        if ($news->status !== 'SENT_WAITING_VERIFICATION') {
            return response()->json(['message' => 'Berita ini tidak berada dalam antrean verifikasi.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $news->status;
            $news->status = 'PUBLISHED'; // Sesuai spesifikasi, setelah disetujui langsung terbit
            $news->save();

            NewsStatusLog::create([
                'news_id'    => $news->id,
                'user_id'    => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $news->status,
                'reason'     => 'Berita disetujui dan diterbitkan.'
            ]);

            // Berikan poin kepada Penulis (Membuat Berita)
            if ($news->user) {
                PointService::awardPoint($news->user, 'Membuat Berita', "Membuat berita: {$news->title}");
            }

            // Berikan poin kepada Editor (Verifikasi Berita)
            PointService::awardPoint(Auth::user(), 'Verifikasi Berita', "Memverifikasi berita: {$news->title}");

            // Notify Author
            if ($news->user) {
                $news->user->notify(new NewsStatusUpdatedNotification($news, 'PUBLISHED'));
            }

            DB::commit();
            return response()->json(['message' => 'Berita berhasil disetujui dan dipublikasikan.', 'status' => $news->status]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    // 3. Fungsi untuk Editor MENOLAK berita
    public function reject(Request $request, $id)
    {
        // Validasi wajib isi alasan
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10'
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi agar penulis bisa merevisi.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $news = News::findOrFail($id);

        if ($news->status !== 'SENT_WAITING_VERIFICATION') {
            return response()->json(['message' => 'Status berita tidak valid untuk ditolak.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $news->status;
            $news->status = 'DRAFT'; // Kembalikan ke Draft agar bisa diedit ulang oleh pembuat
            $news->save();

            NewsStatusLog::create([
                'news_id'    => $news->id,
                'user_id'    => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => 'REJECTED',
                'reason'     => $request->reason // Alasan dari inputan Editor di Frontend
            ]);

            // Notify Author
            if ($news->user) {
                $preferences = $news->user->userPreference ? $news->user->userPreference->preferences : [];
                $onlyApproved = isset($preferences['only_approved_notifications']) ? $preferences['only_approved_notifications'] : false;

                // Jika preferensi only_approved_notifications aktif, JANGAN kirim notifikasi penolakan
                if (!$onlyApproved) {
                    $news->user->notify(new NewsStatusUpdatedNotification($news, 'REJECTED', $request->reason));
                }
            }

            DB::commit();
            return response()->json(['message' => 'Berita ditolak dan dikembalikan menjadi Draft.', 'status' => $news->status]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    // 4. Fungsi untuk Admin melakukan Force Publish
    public function forcePublish(Request $request, $id)
    {
        $news = News::findOrFail($id);

        if ($news->status === 'PUBLISHED') {
            return response()->json(['message' => 'Berita sudah dipublikasikan.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $news->status;
            $news->status = 'PUBLISHED';
            $news->save();

            NewsStatusLog::create([
                'news_id'    => $news->id,
                'user_id'    => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $news->status,
                'reason'     => 'Diterbitkan secara paksa oleh Admin.'
            ]);

            // Notify Author
            if ($news->user && $news->user_id !== Auth::id()) {
                $news->user->notify(new NewsStatusUpdatedNotification($news, 'PUBLISHED'));
            }

            DB::commit();
            return response()->json(['message' => 'Berita berhasil diterbitkan secara paksa.', 'status' => $news->status]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    // 5. Fungsi untuk mengambil Audit Trail (Riwayat Status) dari sebuah berita
    public function auditTrail($id)
    {
        $news = News::findOrFail($id);
        
        // Ambil log status beserta relasi actor (user) yang melakukan perubahan
        $logs = $news->statusLogs()->with('actor:id,name,role')->get();

        return response()->json($logs);
    }
}