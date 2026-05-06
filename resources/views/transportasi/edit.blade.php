<form action="/transportasi/update/{{ $data->id }}" method="POST">
    @csrf

    <select name="kendaraan_id">
        @foreach ($kendaraan as $item)
            <option value="{{ $item->id }}"
                {{ $data->kendaraan_id == $item->id ? 'selected' : '' }}>
                {{ $item->nama_kendaraan }}
            </option>
        @endforeach
    </select>

    <input type="number" name="jarak_km" value="{{ $data->jarak_km }}">
    <input type="date" name="tanggal" value="{{ $data->tanggal }}">

    <button type="submit">Update</button>
</form>