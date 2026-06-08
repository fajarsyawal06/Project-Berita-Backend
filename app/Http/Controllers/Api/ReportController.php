<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportTemplate;
use App\Services\ReportGeneratorService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportGeneratorService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getTemplates()
    {
        $templates = ReportTemplate::where('is_active', true)->get(['id', 'name', 'type']);
        return response()->json(['status' => 'success', 'data' => $templates]);
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_type' => 'required|string|exists:report_templates,type',
            'periode' => 'required|date_format:Y-m',
            'satuan_kerja_id' => 'nullable|exists:satuan_kerjas,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $satuanKerjaId = $request->satuan_kerja_id;

        // Security check: KSK (P-03) only allowed to generate for their own unit
        if ($user && $user->role && $user->role->kode_role === 'P-03') {
            if ($satuanKerjaId != $user->satuan_kerja_id && $satuanKerjaId !== null) {
                return response()->json(['message' => 'Anda hanya dapat men-generate laporan untuk satuan kerja Anda sendiri.'], 403);
            }
            $satuanKerjaId = $user->satuan_kerja_id;
        }

        try {
            $pdf = $this->reportService->generate(
                $request->template_type,
                $request->periode,
                $satuanKerjaId
            );

            // Return file as download
            $filename = 'Laporan_' . $request->template_type . '_' . $request->periode . '.pdf';
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
