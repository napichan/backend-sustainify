<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Transportasi</title>
</head>
<body>

    <h2>Riwayat Aktivitas Transportasi</h2>

   
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="10">
        <tr>
            <th>No</th>
            <th>Kendaraan</th>
            <th>Jarak (km)</th>
            <th>Emisi Karbon</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        @foreach ($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->kendaraan->nama_kendaraan }}</td>
            <td>{{ $item->jarak_km }}</td>
            <td>{{ $item->emisi_karbon }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
            <td>
                <a href="/transportasi/edit/{{ $item->id }}">Edit</a>
                |
                <a href="/transportasi/delete/{{ $item->id }}" 
                   onclick="return confirm('Yakin mau hapus?')">
                   Hapus
                </a>
            </td>
        </tr>
        @endforeach
    </table>

</body>
</html>