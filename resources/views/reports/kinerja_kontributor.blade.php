<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Kontributor</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kinerja Kontributor</h1>
        <p>Satuan Kerja: <strong>{{ $satuan_kerja }}</strong> | Periode: <strong>{{ $periode }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 45%;">Nama Kontributor</th>
                <th style="width: 25%; text-align: center;">Jumlah Berita Terpublikasi</th>
                <th style="width: 25%; text-align: center;">Total Viewers</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contributors as $index => $row)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $row->user ? $row->user->nama_lengkap : 'User Tidak Diketahui' }}</td>
                <td style="text-align: center;">{{ $row->total_news }}</td>
                <td style="text-align: center;">{{ number_format($row->total_views, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">Belum ada data kontributor yang mempublikasikan berita pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh Sistem Manajemen Berita &copy; {{ date('Y') }}
    </div>
</body>
</html>
