<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ad-hoc</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; color: #333; }
        .header p { margin: 5px 0 0 0; font-size: 10pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kustom (Ad-hoc)</h1>
        <p>Dicetak pada: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach ($columns as $col)
                    <th>{{ $columnLabels[$col] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach ($columns as $col)
                        <td>{{ $row->$col }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" style="text-align: center;">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
