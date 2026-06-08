<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Satuan Kerja</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        .section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; color: #1e40af; }
        .grid { display: table; width: 100%; margin-bottom: 20px; border-spacing: 10px; }
        .card { display: table-cell; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; text-align: center; border-radius: 8px; width: 25%; }
        .card-title { font-size: 12px; color: #64748b; margin-bottom: 5px; text-transform: uppercase; }
        .card-value { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Bulanan Satuan Kerja</h1>
        <p>Satuan Kerja: <strong>{{ $satuan_kerja }}</strong> | Periode: <strong>{{ $periode }}</strong></p>
    </div>

    <div class="section-title">Ringkasan Kinerja</div>
    
    <div class="grid">
        <div class="card">
            <div class="card-title">Total Berita Terpublikasi</div>
            <p class="card-value">{{ $total_news }}</p>
        </div>
        <div class="card">
            <div class="card-title">Total Viewers</div>
            <p class="card-value">{{ number_format($total_viewers, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <div class="card-title">Peringkat Nasional</div>
            <p class="card-value">#{{ $national_ranking }}</p>
        </div>
        <div class="card">
            <div class="card-title">Top Kontributor</div>
            <p class="card-value" style="font-size: 14px; margin-top: 5px;">{{ $top_contributor_name }}</p>
            <p style="font-size: 10px; color: #64748b; margin: 2px 0 0;">({{ $top_contributor_news }} berita)</p>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <p style="font-style: italic; color: #64748b; font-size: 11px;">
            Laporan ini di-generate otomatis oleh Sistem Manajemen Berita pada {{ date('d F Y H:i:s') }}. Data di atas merupakan akumulasi metrik yang tercatat di dalam basis data untuk periode yang dipilih.
        </p>
    </div>

    <div class="footer">
        Dicetak oleh Sistem Manajemen Berita &copy; {{ date('Y') }}
    </div>
</body>
</html>
