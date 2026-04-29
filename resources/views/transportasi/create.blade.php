<h2>Input Aktivitas Transportasi</h2>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="/transportasi">
    @csrf

    <label>Jarak (km):</label>
    <input type="number" name="jarak_km" required><br><br>

    <label>Kendaraan:</label>
    <select name="kendaraan_id">
        @foreach($kendaraan as $k)
            <option value="{{ $k->id }}">{{ $k->nama_kendaraan }}</option>
        @endforeach
    </select><br><br>

    <label>Tanggal:</label>
    <input type="date" name="tanggal" required><br><br>

    <button type="submit">Simpan</button>
</form>