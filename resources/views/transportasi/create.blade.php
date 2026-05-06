<form action="/transportasi" method="POST">
    @csrf

    
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    
    <label>Pilih Kendaraan</label><br>
    <select name="kendaraan_id" required>
        <option value="">-- Pilih Kendaraan --</option>
        @foreach ($kendaraan as $item)
            <option value="{{ $item->id }}">
                {{ $item->nama_kendaraan }}
            </option>
        @endforeach
    </select>

    <br><br>

    
    <label>Jarak (km)</label><br>
    <input type="number" name="jarak_km" placeholder="Masukkan jarak" required>

    <br><br>

    
    <button type="submit">Simpan</button>
</form>