<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .meta {
            margin-bottom: 20px;
            font-size: 11px;
            color: #555;
            text-align: center;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            border-left: 4px solid #004085;
            margin-bottom: 5px;
        }
        .section-content {
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .attachments {
            margin-top: 20px;
        }
        .attachments .image-box {
            text-align: center;
            margin-bottom: 15px;
        }
        .attachments img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #777;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN BERITA</h1>
        <p>Aplikasi Portal Berita Internal</p>
    </div>

    <div class="title">
        {{ $news->judul }}
    </div>

    <div class="meta">
        <strong>Status:</strong> {{ $news->status }} | 
        <strong>Kategori:</strong> {{ $news->category->nama_kategori ?? '-' }} | 
        <strong>Penulis:</strong> {{ $news->user->nama_lengkap ?? '-' }} 
        ({{ $news->satuanKerja->nama_satker ?? '-' }})<br>
        <strong>Dibuat Pada:</strong> {{ $news->created_at->format('d F Y H:i') }}
    </div>

    <!-- 5W + 1H -->
    <div class="section">
        <div class="section-title">What (Apa yang terjadi?)</div>
        <div class="section-content">
            {{ $news->what_content ?: '-' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Who (Siapa yang terlibat?)</div>
        <div class="section-content">
            {{ $news->who_involved ?: '-' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">When (Kapan terjadinya?)</div>
        <div class="section-content">
            {{ $news->when_occurred ?: '-' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Where (Di mana lokasinya?)</div>
        <div class="section-content">
            {{ $news->where_location ?: '-' }}<br>
            @if($news->location_address)
                <small><i>Alamat: {{ $news->location_address }}</i></small>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Why (Mengapa terjadi?)</div>
        <div class="section-content">
            {{ $news->why_happened ?: '-' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">How (Bagaimana penyelesaiannya?)</div>
        <div class="section-content">
            {{ $news->how_resolved ?: '-' }}
        </div>
    </div>

    <!-- Lampiran (Gambar) -->
    @if($news->attachments && $news->attachments->where('file_type', 'image')->count() > 0)
    <div class="attachments">
        <div class="section-title">Lampiran Media (Gambar)</div>
        @foreach($news->attachments->where('file_type', 'image') as $img)
            <div class="image-box">
                <?php 
                    // Konversi ke base64 untuk dompdf agar mudah dirender
                    $path = storage_path('app/public/' . $img->file_path);
                    if(file_exists($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        echo '<img src="'.$base64.'" alt="Lampiran">';
                    }
                ?>
                <p><i>{{ $img->original_filename }}</i></p>
            </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        Dicetak oleh sistem pada: {{ $date }}<br>
        Dokumen ini dibuat otomatis dan tidak memerlukan tanda tangan basah.
    </div>

</body>
</html>
