<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdhocReportController extends Controller
{
    // Daftar kolom yang diizinkan untuk dipilih oleh user
    protected $allowedColumns = [
        'id' => 'ID Berita',
        'judul_berita' => 'Judul Berita',
        'status' => 'Status',
        'jumlah_viewers' => 'Jumlah Viewers',
        'tanggal_dibuat' => 'Tanggal Dibuat',
        'tanggal_publish' => 'Tanggal Publish',
        'nama_pembuat' => 'Nama Pembuat',
        'satuan_kerja' => 'Satuan Kerja',
        'kategori_berita' => 'Kategori Berita'
    ];

    public function getColumns()
    {
        $columns = [];
        foreach ($this->allowedColumns as $key => $label) {
            $columns[] = [
                'id' => $key,
                'label' => $label
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $columns
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'columns' => 'required|array|min:1',
            'filters' => 'nullable|array'
        ]);

        $query = $this->buildQuery($request->columns, $request->filters);
        
        // Limit to 100 rows for preview
        $data = $query->limit(100)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'columns' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'format' => 'required|in:pdf,csv'
        ]);

        $query = $this->buildQuery($request->columns, $request->filters);
        $data = $query->get(); // No limit for export

        $format = $request->format;
        $columns = $request->columns;
        $columnLabels = [];
        foreach ($columns as $col) {
            $columnLabels[$col] = $this->allowedColumns[$col] ?? $col;
        }

        if ($format === 'csv') {
            return $this->exportCsv($data, $columns, $columnLabels);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($data, $columns, $columnLabels);
        }
    }

    private function buildQuery($selectedColumns, $filters)
    {
        // Validasi kolom agar terhindar dari SQL Injection
        $safeColumns = array_intersect($selectedColumns, array_keys($this->allowedColumns));
        if (empty($safeColumns)) {
            $safeColumns = ['id'];
        }

        $query = DB::table('v_news_complete')->select($safeColumns);

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
                    $field = $filter['field'];
                    $operator = $filter['operator'];
                    $value = $filter['value'];

                    // Pastikan field aman
                    if (array_key_exists($field, $this->allowedColumns)) {
                        if ($operator === 'LIKE') {
                            $query->where($field, 'LIKE', '%' . $value . '%');
                        } else {
                            $query->where($field, $operator, $value);
                        }
                    }
                }
            }
        }

        return $query;
    }

    private function exportCsv($data, $columns, $columnLabels)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Laporan_Adhoc_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($data, $columns, $columnLabels) {
            $file = fopen('php://output', 'w');
            
            // Tulis Header
            $headerRow = [];
            foreach ($columns as $col) {
                $headerRow[] = $columnLabels[$col];
            }
            fputcsv($file, $headerRow);

            // Tulis Data
            foreach ($data as $row) {
                $dataRow = [];
                foreach ($columns as $col) {
                    $dataRow[] = $row->$col;
                }
                fputcsv($file, $dataRow);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPdf($data, $columns, $columnLabels)
    {
        $pdf = Pdf::loadView('reports.adhoc_pdf', [
            'data' => $data,
            'columns' => $columns,
            'columnLabels' => $columnLabels
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Laporan_Adhoc_' . date('Ymd_His') . '.pdf');
    }
}
