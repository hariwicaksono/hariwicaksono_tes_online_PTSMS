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
            <td>{{ $log->created_at }}</td>
            <td>{{ $log->user->name ?? '-' }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->module }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->ip_address }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
