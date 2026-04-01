<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak Log Aktivitas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <h2>Log Aktivitas</h2>
    <p>Dicetak: {{ now()->format('d-m-Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>User</th>
                <th>Aksi</th>
                <th>Modul</th>
                <th>Keterangan</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $log->user->name ?? '-' }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->module }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
